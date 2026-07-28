<?php

namespace App\Controller;

use App\Entity\FileImport;
use App\Enum\FileTypeEnum;
use App\Form\UploadFileFormType;
use App\Handler\ImportFileHandler;
use App\Repository\FileImportRepository;
use App\Service\ImporterService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Console\Messenger\RunCommandMessage;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Service\Attribute\Required;

final class HomeController extends AbstractController
{
    private MessageBusInterface $bus;

    public function __construct(
        protected EntityManagerInterface $em,
        protected ImporterService $importerService,
        protected FileImportRepository $fileImportRepository,
        protected ImportFileHandler $importFileHandler
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

                $this->importFileHandler->handleUploadedFile($fileImport, $name, $type);

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
            $this->bus->dispatch(new RunCommandMessage(
                sprintf(
                    'app:import-document %s --type=%s', 
                    $fileId, 
                    $fileType)
            ));
            
            $this->addFlash('success', "Import started.");

            return $this->redirectToRoute('app_home');

        } catch (Exception $e) {
            throw new \RuntimeException("Error during import process: {$e->getMessage()}");
        }

    }


    public function normalizeType(UploadedFile $file): string
    {
        $allowed = ['csv', 'json', 'xml', 'xlsx'];

        $type = $file->guessExtension();

        /**
         * guessExtension() maps csv to txt. For that reason it needs additional validation by checking
         * the client mime type to confirm wether it's truly csv or a generic txt type.
         */
        if ('txt' === $type) {
            $type = 'text/csv' === $file->getClientMimeType() ? 'csv' : $type;
        }

        if (!in_array($type, $allowed, true)) {
            throw new \RuntimeException("The file type '{$type}' is not allowed.");
        }

        return $type;
    }

}
