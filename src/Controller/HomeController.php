<?php

namespace App\Controller;

use App\Entity\ImportFile;
use App\Enum\FileTypeEnum;
use App\Form\UploadFileFormType;
use App\Handler\ImportFileUploadHandler;
use App\Message\ImportFileMessage;
use App\Repository\ImportFileRepository;
use App\Service\ImporterService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Service\Attribute\Required;

final class HomeController extends AbstractController
{
    private MessageBusInterface $bus;

    public function __construct(
        protected EntityManagerInterface $em,
        protected ImporterService $importerService,
        protected ImportFileRepository $importFileRepository,
        protected ImportFileUploadHandler $importFileUploadHandler
    )
    {
    }

    #[Required]
    public function setMessageBus(MessageBusInterface $bus): void
    {
        $this->bus = $bus;
    }

    #[Route('/', name: 'app_home')]
    public function index(Request $request): Response
    {
        try {
            $importFile = new ImportFile();

            $files = $this->importFileRepository->findAll();

            $form = $this->createForm(UploadFileFormType::class, $importFile);

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $this->importFileUploadHandler->handleUploadedFile($importFile);

                $this->addFlash('success', 'File upload complete');

                return $this->redirectToRoute('app_home');
            }

            return $this->render('home/index.html.twig', [
                'controller_name' => 'HomeController',
                'form' => $form->createView(),
                'files' => $files
            ]);
        
        } catch(\Exception $e) {
            throw new \Exception("Exception while creating form: {$e}");
        }
    }

    #[Route('/import/{fileId}', name: 'app_import')]
    public function startImport(Request $request, int $fileId): Response
    {
        $file = $this->importFileRepository->find($fileId);
        $fileType = FileTypeEnum::extension($file->fileType);

        if (null === $fileType) {
            throw new \RuntimeException("No file found for file id: {$fileId}");
        }

        try {        
            $this->bus->dispatch(new ImportFileMessage($fileId, $fileType));            

            $this->addFlash('success', "Import started.");

            return $this->redirectToRoute('app_home');

        } catch (Exception $e) {
            throw new \RuntimeException("Error during import process: {$e->getMessage()}");
        }

    }

    #[Route('/delete-import/{fileId}', name: 'app_delete_file')]
    public function deleteFile(int $fileId, Request $request): Response
    {
        $file = $this->importFileRepository->find($fileId);

        if (!$this->isCsrfTokenValid("delete-{$fileId}", $request->request->get('_token'))) {
            throw $this->createAccessDeniedException();
        }

        $this->importFileUploadHandler->deleteUploadedFile($file);
        $this->addFlash('success', 'File deleted successfully.');

        return $this->redirectToRoute('app_home');
    }
}
