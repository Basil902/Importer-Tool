<?php

namespace App\Tests\Unit\Service;

use App\Entity\FileImport;
use App\Handler\ImportUserHandlerInterface;
use App\Import\Reader\Strategy\ReaderInterface;
use App\Service\BatchService;
use App\Service\ImporterService;
use App\Service\ImportFileLocator;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class ImporterServiceTest extends TestCase
{
    # &MockObject is used to define an intersection type, which is both the class on the left and a mock object.
    private ReaderInterface&MockObject $csvImportReader;
    private ReaderInterface&MockObject $excelImportReader;
    private ImporterService $importerService;
    private FileImport $fileImport;

    protected function setUp(): void
    {
        $em = $this->createStub(EntityManagerInterface::class);
        $importUserHandler = $this->createStub(ImportUserHandlerInterface::class);
        $batchService = $this->createStub(BatchService::class);
        $this->csvImportReader = $this->createMock(ReaderInterface::class);
        $this->excelImportReader = $this->createMock(ReaderInterface::class);
        $this->fileImport = $this->createStub(FileImport::class);

        $this->importerService = new ImporterService(
            $em,
            $importUserHandler,
            $batchService,
            [$this->csvImportReader, $this->excelImportReader],
        );

        $fileImportLocator = $this->createStub(ImportFileLocator::class);
        $this->importerService->setImportFileLocator($fileImportLocator);
    }

    public function testThrowsWhenNoReaderSupportsType(): void
    {
        $this->expectException(RuntimeException::class);

        $this->csvImportReader
            ->expects($this->once())
            ->method('supports')
            ->willReturn(false);
        $this->excelImportReader
            ->expects($this->once())
            ->method('supports')
            ->willReturn(false);

        $this->importerService->execute($this->fileImport, 'xml', 1);
    }
}