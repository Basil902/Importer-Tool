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
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Process\Process;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    public function __construct(
        protected EntityManagerInterface $em,
        protected ImporterService $importerService,
        protected FileImportRepository $fileImportRepository,
        protected ImportFileHandler $importFileHandler
    )
    {
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
                
                // $fileType = $tw

                // $fileName = $file->getClientOriginalName();

                // $this->importerService->execute($fileImport, $fileName, $fileType);

                // $userCount = $this->importerService->getSavedUserCount();

                // if (0 !== $userCount) {
                //     $this->addFlash('success', "Import successful, {$userCount} users saved to the database.");
                // } else {
                //     $this->addFlash('danger', 'Something went wrong during the import. No users saved to the database.');
                // }

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
    public function startImport(Request $request, int $fileId): void
    {
        /**
         * Eine sicherere Variante, um den Command zu erstellen wäre direkt in einem array mit separaten Werte:
         * $process = new Process([
         *  'php',
         *  'bin/console',
         *  'app:import-file',
         *  (string) $fileId,
         * ]);
         */

        $file = $this->fileImportRepository->find($fileId);
        $fileType = FileTypeEnum::extension($file->fileType);

        if (null === $fileType) {
            throw new \RuntimeException("No file found for file id: {$fileId}");
        }

        try {

            // working directory, it's necessary for command
            $workDir = $this->getParameter('kernel.project_dir');


            $command = sprintf(
                'php bin/console app:import-document %s --type=%s > var/log/import.log 2>&1 &', 
                $fileId, 
                $fileType);

            dd($command);

            $process = Process::fromShellCommandline($command);

            $process->setWorkingDirectory($workDir);

            $process->run();


            $output = $process->getOutput();
            dd("HERE");
            $userCount = $this->importerService->getSavedUserCount();

            if (0 !== $userCount) {
                $this->addFlash('success', "Import successful, {$userCount} users saved to the database.");
            } else {
                $this->addFlash('danger', 'Something went wrong during the import. No users saved to the database.');
            }

            $this->redirectToRoute('app_home');

        } catch (Exception $e) {
            throw new \RuntimeException("Error during import process: {$e->getMessage()}");
        }

    }

    // testing route for testing new functions
    // #[Route('/test', name: 'app_test')]
    // public function test()
    // {
    //     $file = $this->getParameter('kernel.project_dir') . '/var/uploads/test.csv';
    //     $test2 = [];

    //     $stream = fopen($file, 'r');

    //     while (($row = fgetcsv($stream, separator: ';')) !== false) {
    //         dump($row);
    //     }
    //     // foreach ($row = fgetcsv($stream, separator: ';') as $data) {
    //     //     dump($row);
    //     // }

    //     // VON CHATGPT:
    //     // while (($row = fgetcsv($stream, separator: ";")) !== false) {
    //     //     print_r($row);
    //     // }

    //     // $test = fgetcsv($stream, separator: ';');

    //     dd($test2);
    // }

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
