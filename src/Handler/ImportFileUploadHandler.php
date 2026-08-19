<?php

namespace App\Handler;

use App\Entity\ImportFile;
use App\Enum\FileTypeEnum;
use App\Enum\ImportStatusEnum;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;

# Note: This is simply a class for handling uploaded import files. It has nothing to do with symfony messenger or messages in general.

final class ImportFileUploadHandler
{
    public function __construct(
        protected EntityManagerInterface $em,
        protected Filesystem $fileSystem,
    )
    {
    }

    public function handleUploadedFile(ImportFile $importFile): void
    {
        $uploadedFile = $importFile->file;

        if (!$uploadedFile instanceof UploadedFile) {
            throw new FileNotFoundException("The import file {$importFile->id} has no associated file. Please make sure to upload a file.");
        }

        $name = $uploadedFile->getClientOriginalName();

        $type = $this->normalizeType($uploadedFile);
        $normalizedType = FileTypeEnum::normalizeExtension($type);

        $importFile->fileName = $name;
        $importFile->fileType = $normalizedType;
        $importFile->status = ImportStatusEnum::STATUS_UPLOADED;

        $this->em->persist($importFile);
        $this->em->flush();
    }

    public function deleteUploadedFile(ImportFile $file)
    {
        if (null === $file->file) {
            throw new \LogicException("Can not delete import file entry with id {$file->id}. The entity has no associated file.");
        }

        $path = $file->file->getPathname();

        $this->em->remove($file);
        $this->em->flush();
        $this->fileSystem->remove($path);
    }

    public function normalizeType(UploadedFile $file): string
    {
        $allowed = ['csv', 'json', 'xml', 'xlsx'];

        $mimeType = $file->getClientMimeType();

        $type = match ($mimeType){
            'text/csv' => 'csv',
            'text/xml' => 'xml',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
            'application/json' => 'json',
            default => 'undefined'
        };

        if (!in_array($type, $allowed, true)) {
            throw new \RuntimeException("The file type '{$type}' is not allowed.");
        }

        return $type;
    }
}