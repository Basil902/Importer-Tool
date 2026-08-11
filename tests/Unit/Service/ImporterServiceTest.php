<?php

namespace App\Tests\Unit\Service;

use App\Entity\FileImport;
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
    private FileImport $fileImport;

    protected function setUp(): void
    {
        $this->em = $this->createStub(EntityManagerInterface::class);
        $this->importUserHandler = $this->createStub(ImportUserHandlerInterface::class);
        $this->batchService = $this->createStub(BatchService::class);
        $this->fileImport = $this->createFileImport();

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

    public function createFileImport(): FileImport
    {
        $fileImport = new FileImport();

        $fileImport->fileName = 'users.csv';
        $fileImport->fileType = FileTypeEnum::CSV;
        $fileImport->uploadedAt = new \DateTimeImmutable();
        $fileImport->updatedAt = new \DateTimeImmutable();
        $fileImport->status = ImportStatusEnum::STATUS_UPLOADED;

        return $fileImport;
    }

    public function testThrowsWhenNoReaderSupportsType(): void
    {
        $this->expectException(RuntimeException::class);

        $this->importerService->execute($this->fileImport, 'csv');
    }

    public function testLocatorExceptionPropagates(): void
    {
        $this->expectException(RuntimeException::class);

        $this->storage = $this->createStub(StorageInterface::class);
        $this->storage->method('resolvePath')->willReturn('tmp/nonexistand-file.csv');
        $this->importerService->setImportFileLocator(new ImportFileLocator($this->storage));

        $this->importerService->execute($this->fileImport, 'csv');
    }
}