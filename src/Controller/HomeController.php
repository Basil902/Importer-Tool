<?php

namespace App\Controller;

use App\Entity\FileImport;
use App\Enum\FileTypeEnum;
use App\Form\UploadFileFormType;
use App\Handler\ImportFileUploadHandler;
use App\Message\ImportFileMessage;
use App\Repository\FileImportRepository;
use App\Service\ImporterService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
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
        protected FileImportRepository $fileImportRepository,
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
            $fileImport = new FileImport();

            $files = $this->fileImportRepository->findAll();

            $form = $this->createForm(UploadFileFormType::class, $fileImport);

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {

                $file = $form->get('file')->getData();

                $name = $file->getClientOriginalName();
                $type = $this->normalizeType($file);

                $this->importFileUploadHandler->handleUploadedFile($fileImport, $name, $type);

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
        $file = $this->fileImportRepository->find($fileId);
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
        $file = $this->fileImportRepository->find($fileId);

        if (!$this->isCsrfTokenValid("delete-{$fileId}", $request->request->get('_token'))) {
            throw $this->createAccessDeniedException();
        }

        $this->importFileUploadHandler->deleteUploadedFile($file);
        $this->addFlash('success', 'File deleted successfully.');

        return $this->redirectToRoute('app_home');
    }

    public function normalizeType(UploadedFile $file): string
    {
        $allowed = ['csv', 'json', 'xml', 'xlsx'];

        $type = $file->guessExtension();

        /**
         * guessExtension() maps some file types to txt. Therefore it needs additional validation by checking
         * the client mime type.
         */
        if ('txt' === $type) {
            $mimeType = $file->getClientMimeType();

            $type = match ($mimeType){
                'text/csv' => 'csv',
                'application/json' => 'json',
                default => 'undefined'
            };
        }

        if (!in_array($type, $allowed, true)) {
            throw new \RuntimeException("The file type '{$type}' is not allowed.");
        }

        return $type;
    }
}
