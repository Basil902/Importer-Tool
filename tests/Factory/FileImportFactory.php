<?php

namespace App\Tests\Factory;

use App\Entity\FileImport;
use App\Enum\FileTypeEnum;
use App\Enum\ImportStatusEnum;

final class FileImportFactory
{
    public function create(string $name, FileTypeEnum $type): FileImport
    {
        $fileImport = new FileImport();
        $fileImport->fileName = $name;
        $fileImport->fileType = $type;
        $fileImport->status = ImportStatusEnum::STATUS_UPLOADED;
        $fileImport->uploadedAt = new \DateTimeImmutable();
        $fileImport->updatedAt = new \DateTimeImmutable();

        return $fileImport;
    }
}