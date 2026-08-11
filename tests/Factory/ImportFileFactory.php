<?php

namespace App\Tests\Factory;

use App\Entity\ImportFile;
use App\Enum\FileTypeEnum;
use App\Enum\ImportStatusEnum;

final class ImportFileFactory
{
    public function create(string $name, FileTypeEnum $type): ImportFile
    {
        $importFile = new ImportFile();
        $importFile->fileName = $name;
        $importFile->fileType = $type;
        $importFile->status = ImportStatusEnum::STATUS_UPLOADED;
        $importFile->uploadedAt = new \DateTimeImmutable();
        $importFile->updatedAt = new \DateTimeImmutable();

        return $importFile;
    }
}