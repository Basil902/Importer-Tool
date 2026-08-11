<?php

namespace App\Tests\Integration;

use App\Entity\FileImport;
use App\Enum\FileTypeEnum;
use App\Enum\ImportStatusEnum;
use App\Import\UnreadeableFileException;
use App\Repository\UserRepository;
use App\Service\ImporterService;
use App\Tests\Factory\FileImportFactory;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Exception\MissingColumnException;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class ImporterServiceTest extends KernelTestCase
{
    private FileImport $fileImportCsv;
    private FileImport $fileImportMissingColumns;
    private FileImport $fileImportEmptyCsv;
    private FileImport $fileImportEmptyExcel;
    private FileImport $fileImportEmptyJson;
    private FileImport $fileImportEmptyXml;
    private EntityManagerInterface $em;
    private UserRepository $userRepository;

    public function setUp(): void
    {
        self::bootKernel();
        $this->em = self::getContainer()->get(EntityManagerInterface::class);
        $this->userRepository = self::getContainer()->get(UserRepository::class);
        $factory = new FileImportFactory();

        $this->fileImportCsv = $factory->create('users.csv', FileTypeEnum::CSV);
        $this->fileImportMissingColumns = $factory->create('missing_columns.csv', FileTypeEnum::CSV);
        $this->fileImportEmptyCsv = $factory->create('empty_file.csv', FileTypeEnum::CSV);
        $this->fileImportEmptyExcel = $factory->create('empty_file.xlsx', FileTypeEnum::EXCEL);
        $this->fileImportEmptyJson = $factory->create('empty_file.json', FileTypeEnum::JSON);
        $this->fileImportEmptyXml = $factory->create('empty_file.xml', FileTypeEnum::XML);


        $this->em->persist($this->fileImportCsv);
        $this->em->persist($this->fileImportMissingColumns);
        $this->em->persist($this->fileImportEmptyCsv);
        $this->em->persist($this->fileImportEmptyExcel);
        $this->em->persist($this->fileImportEmptyJson);
        $this->em->persist($this->fileImportEmptyXml);
        $this->em->flush();
    }

    public function testThrowsWhenMissingColumnsInFile(): void
    {   
        $this->expectException(MissingColumnException::class);

        $importerService = self::getContainer()->get(ImporterService::class);

        $type = $this->getType($this->fileImportMissingColumns);

        $importerService->execute($this->fileImportMissingColumns, $type);
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

        $type = $this->getType($this->fileImportCsv);
        
        $importerService->execute($this->fileImportCsv, $type);
        $this->em->refresh($this->fileImportCsv);
        $user = $this->userRepository->findOneBy(['email' => 'david@test.com']);


        $this->assertSame(ImportStatusEnum::STATUS_PROCESSED, $this->fileImportCsv->status);
        $this->assertSame(10, $this->userRepository->count([]));
        $this->assertNotNull($user);
        $this->assertSame('david@test.com', $user->email);
        $this->assertSame('dev', $user->role);
    }

    protected function emptyFileFor(string $type): FileImport
    {
        return match ($type) {
            'csv' => $this->fileImportEmptyCsv,
            'xlsx' => $this->fileImportEmptyExcel,
            'json' => $this->fileImportEmptyJson,
            'xml' => $this->fileImportEmptyXml,
            default => throw new \RuntimeException("Undefined type '{$type}'."),
        };
    }

    protected function getType(FileImport $file): string
    {
        return $file->fileType->value;
    }
}