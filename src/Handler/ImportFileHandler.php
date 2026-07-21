<?php

namespace App\Handler;

use App\Entity\FileImport;
use App\Enum\FileTypeEnum;
use Doctrine\ORM\EntityManagerInterface;

final class ImportFileHandler
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_PROCESSED = 'processed';
    public const STATUS_ERROR = 'error';

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
        $file->status = self::STATUS_PENDING;

        $this->em->persist($file);
        $this->em->flush();
    }
}