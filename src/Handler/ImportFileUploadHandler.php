<?php

namespace App\Handler;

use App\Entity\ImportFile;
use App\Enum\FileTypeEnum;
use App\Enum\ImportStatusEnum;
use App\Service\ImportFileLocator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;

# Note: This is simply a class for handling uploaded import files. It has nothing to do with symfony messenger or messages in general.

final class ImportFileUploadHandler
{
    public function __construct(
        protected EntityManagerInterface $em,
        protected Filesystem $fileSystem,
        protected ImportFileLocator $importFileLocator
    )
    {
    }

    public function handleUploadedFile(ImportFile $file, string $name, string $type): void
    {
        $normalizedType = FileTypeEnum::normalizeExtension($type);

        $file->fileName = $name;
        $file->fileType = $normalizedType;
        $file->status = ImportStatusEnum::STATUS_UPLOADED;

        $this->em->persist($file);
        $this->em->flush();
    }

    public function deleteUploadedFile(ImportFile $file)
    {
        $path = $this->importFileLocator->getFileToImport($file);

        if (!$this->fileSystem->exists($path)) {
            throw new \RuntimeException("The file {$file->id} could not be deleted. Please check if the file exists.");
        }

        $this->em->remove($file);
        $this->em->flush();
        $this->fileSystem->remove($path);
    }
}