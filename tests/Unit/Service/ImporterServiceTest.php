<?php

namespace App\Tests\Unit\Service;

use App\Entity\ImportFile;
use App\Enum\FileTypeEnum;
use App\Enum\ImportStatusEnum;
use App\Handler\ImportUserHandlerInterface;
use App\Service\BatchService;
use App\Service\ImporterService;
use App\Service\ImportFileLocator;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Vich\UploaderBundle\Storage\StorageInterface;

final class ImporterServiceTest extends TestCase
{
    # &Stub is used to define an intersection type, which is both the class on the left and a mock object.
    private EntityManagerInterface&Stub $em;
    private ImportUserHandlerInterface&Stub $importUserHandler;
    private BatchService&Stub $batchService;
    private StorageInterface&Stub $storage;
    private ImporterService $importerService;
    private ImportFile $importFile;

    protected function setUp(): void
    {
        $this->em = $this->createStub(EntityManagerInterface::class);
        $this->importUserHandler = $this->createStub(ImportUserHandlerInterface::class);
        $this->batchService = $this->createStub(BatchService::class);
        $this->importFile = $this->createImportFile();

        $this->importerService = new ImporterService(
            $this->em,
            $this->importUserHandler,
            $this->batchService,
            [],
        );

        $this->storage = $this->createStub(StorageInterface::class);
        $this->storage->method('resolvePath')->willReturn(__DIR__.'/Fixtures/Files/users.csv');
        
        $this->importerService->setImportFileLocator(new ImportFileLocator($this->storage));
    }

    public function createimportFile(): ImportFile
    {
        $importFile = new ImportFile();

        $importFile->fileName = 'users.csv';
        $importFile->fileType = FileTypeEnum::CSV;
        $importFile->uploadedAt = new \DateTimeImmutable();
        $importFile->updatedAt = new \DateTimeImmutable();
        $importFile->status = ImportStatusEnum::STATUS_UPLOADED;

        return $importFile;
    }

    public function testThrowsWhenNoReaderSupportsType(): void
    {
        $this->expectException(RuntimeException::class);

        $this->importerService->execute($this->importFile, 'csv');
    }

    public function testLocatorExceptionPropagates(): void
    {
        $this->expectException(RuntimeException::class);

        $this->storage = $this->createStub(StorageInterface::class);
        $this->storage->method('resolvePath')->willReturn('tmp/nonexistand-file.csv');
        $this->importerService->setImportFileLocator(new ImportFileLocator($this->storage));

        $this->importerService->execute($this->importFile, 'csv');
    }
}