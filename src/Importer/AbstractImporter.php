<?php

namespace App\Importer;

use App\DTO\UserDTO;
use App\Entity\FileImport;
use App\Handler\ImportUserHandler;
use App\Importer\Strategy\ImporterInterface;
use App\Repository\FileImportRepository;
use Vich\UploaderBundle\Storage\StorageInterface;

abstract class AbstractImporter implements ImporterInterface
{
    // keep track of saved users during each import
    // später eventuell auch noch skipped user oder invalid users hinzufügen
    public int $savedUserCount = 0;

    public function __construct(
        protected FileImportRepository $fileImportRepository,
        protected StorageInterface $storage,
        protected UserDTO $userDTO,
        protected ImportUserHandler $importUserHandler
    )
    {
    }

    abstract function supports(string $type): bool;
    
    abstract function import(FileImport $file): void;

    abstract protected function extractData(array $data): void;

    // return path of the file that is currently being processed / imported
    protected function getFileToImport(FileImport $file): string
    {
        $filePath = $this->storage->resolvePath($file, 'file');

        if (null === $file || !file_exists($filePath)) {
            throw new \RuntimeException("Path could not be found for file {$filePath}");
        }

        return $filePath;
    }

    public function getSavedUserCount(): int
    {
        return $this->savedUserCount;
    }
}