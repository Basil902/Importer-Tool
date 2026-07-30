<?php

namespace App\Handler;

use App\Entity\FileImport;
use App\Enum\FileTypeEnum;
use App\Enum\ImportStatusEnum;
use Doctrine\ORM\EntityManagerInterface;

# Note: This is simply a class for handling uploaded import files. It has nothing to do with symfony messenger or messages in general.

final class ImportFileUploadHandler
{
    public function __construct(
        protected EntityManagerInterface $em
    )
    {
    }

    public function handleUploadedFile(FileImport $file, string $name, string $type): void
    {
        $normalizedType = FileTypeEnum::normalizeExtension($type);

        $file->fileName = $name;
        $file->fileType = $normalizedType;
        $file->status = ImportStatusEnum::STATUS_UPLOADED;

        $this->em->persist($file);
        $this->em->flush();
    }
}