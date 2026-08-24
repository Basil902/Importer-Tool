<?php

namespace App\Tests\Integration;

use App\Entity\ImportFile;
use App\Enum\FileTypeEnum;
use App\Enum\ImportStatusEnum;
use App\Import\UnreadeableFileException;
use App\Repository\EmployeeRepository;
use App\Service\ImporterService;
use App\Tests\Factory\ImportFileFactory;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Exception\MissingColumnException;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class ImporterServiceTest extends KernelTestCase
{
    private ImportFile $importFileCsv;
    private ImportFile $importFileMissingColumns;
    private ImportFile $importFileEmptyCsv;
    private ImportFile $importFileEmptyExcel;
    private ImportFile $importFileEmptyJson;
    private ImportFile $importFileEmptyXml;
    private ImportFile $malformedEmailXml;
    private EntityManagerInterface $em;
    private EmployeeRepository $employeeRepository;
    private string $logFilePath;

    public function setUp(): void
    {
        self::bootKernel();
        $this->em = self::getContainer()->get(EntityManagerInterface::class);
        $this->employeeRepository = self::getContainer()->get(EmployeeRepository::class);
        $this->logFilePath = dirname(__DIR__, 3).'/var/log/import_error.log';
        $factory = new ImportFileFactory();

        $this->importFileCsv = $factory->create('employees.csv', FileTypeEnum::CSV);
        $this->importFileMissingColumns = $factory->create('missing_columns.csv', FileTypeEnum::CSV);
        $this->importFileEmptyCsv = $factory->create('empty_file.csv', FileTypeEnum::CSV);
        $this->importFileEmptyExcel = $factory->create('empty_file.xlsx', FileTypeEnum::EXCEL);
        $this->importFileEmptyJson = $factory->create('empty_file.json', FileTypeEnum::JSON);
        $this->importFileEmptyXml = $factory->create('empty_file.xml', FileTypeEnum::XML);
        $this->malformedEmailXml = $factory->create('malformed_email.xml', FileTypeEnum::XML);

        $this->em->persist($this->importFileCsv);
        $this->em->persist($this->importFileMissingColumns);
        $this->em->persist($this->importFileEmptyCsv);
        $this->em->persist($this->importFileEmptyExcel);
        $this->em->persist($this->importFileEmptyJson);
        $this->em->persist($this->importFileEmptyXml);
        $this->em->persist($this->malformedEmailXml);
        $this->em->flush();
    }

    public function testThrowsWhenMissingColumnsInFile(): void
    {   
        $this->expectException(MissingColumnException::class);

        $importerService = self::getContainer()->get(ImporterService::class);

        $type = $this->getType($this->importFileMissingColumns);

        $importerService->execute($this->importFileMissingColumns, $type);
    }

    public static function emptyFileProvider(): array
    {
        return [
            'csv' => ['csv'],
            'xlsx' => ['xlsx'],
            'json' => ['json'],
            'xml' => ['xml'],
        ];
    }

    #[DataProvider('emptyFileProvider')]
    public function testThrowsWhenFileIsEmpty(string $type): void
    {
        $this->expectException(UnreadeableFileException::class);

        $importerService = self::getContainer()->get(ImporterService::class);
        $emptyFileFixture = $this->emptyFileFor($type);

        $importerService->execute($emptyFileFixture, $type);
    }

    public function testEndToEndImportProcessSucceeds(): void
    {
        $importerService = self::getContainer()->get(ImporterService::class);

        $type = $this->getType($this->importFileCsv);
        
        $importerService->execute($this->importFileCsv, $type);
        $this->em->refresh($this->importFileCsv);
        $employee = $this->employeeRepository->findOneBy(['email' => 'david@test.com']);


        $this->assertSame(ImportStatusEnum::STATUS_PROCESSED, $this->importFileCsv->status);
        $this->assertSame(10, $this->employeeRepository->count([]));
        $this->assertNotNull($employee);
        $this->assertSame('david@test.com', $employee->email);
        $this->assertSame('dev', $employee->role);
    }

    public function testWritesToLogFileIfMalformedRow(): void
    {
        $importerService = self::getContainer()->get(ImporterService::class);

        $type = $this->getType($this->malformedEmailXml);

        $importerService->execute($this->malformedEmailXml, $type);

        $content = file_get_contents($this->logFilePath);

        $this->assertStringNotEqualsFile($this->logFilePath, '');
        $this->assertStringContainsString('Error while processing file with ID', $content);
    }

    protected function emptyFileFor(string $type): ImportFile
    {
        return match ($type) {
            'csv' => $this->importFileEmptyCsv,
            'xlsx' => $this->importFileEmptyExcel,
            'json' => $this->importFileEmptyJson,
            'xml' => $this->importFileEmptyXml,
            default => throw new \RuntimeException("Undefined type '{$type}'."),
        };
    }

    protected function getType(ImportFile $file): string
    {
        return $file->fileType->value;
    }

    protected function tearDown(): void
    {
        if (isset($this->logFilePath)) {
            file_put_contents($this->logFilePath, "");   
        }
        parent::tearDown();
    }
}