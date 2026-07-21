<?php

namespace App\Command;

use App\Repository\FileImportRepository;
use App\Service\ImporterService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:import-document',
    description: 'Starts the import process for an uploaded file.',
)]
class ImportDocumentCommand extends Command
{
    public function __construct(
        protected ImporterService $importerService,
        protected FileImportRepository $fileImportRepository
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('id', null, InputArgument::REQUIRED, 'file id')
            ->addOption('type', null, InputOption::VALUE_REQUIRED, 'The type of the file (json, xml, csv, xlsx)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $id = $input->getArgument('id');
        $type = $input->getOption('type');

        if ($type) {
            $io->info("Starting importing for document with type {$type}");
        }

        $file = $this->fileImportRepository->find($id);


        if (!$file) {
            throw new \RuntimeException("No file with id '{$id}' and type '{$type}' found.");
        }

        // $io->info("File found: {$file->fileName}, extracting data..");

        $this->importerService->execute($file, $type);

        $io->success('File import finished.');

        return Command::SUCCESS;
    }
}
