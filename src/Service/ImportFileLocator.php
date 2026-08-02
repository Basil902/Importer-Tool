<?php

namespace App\Service;

use App\Entity\FileImport;
use Vich\UploaderBundle\Storage\StorageInterface;

final class ImportFileLocator
{
    public function __construct(
        protected StorageInterface $storage
    )
    {
    }

    // return path of the file that needs to be imported
    public function getFileToImport(FileImport $file): string
    {
        $filePath = $this->storage->resolvePath($file, 'file');

        if (null === $file || !file_exists($filePath)) {
            throw new \RuntimeException("Path could not be found for file {$filePath}");
        }

        return $filePath;
    }
}