<?php

namespace App\Tests\Unit\Handler;

use App\Entity\ImportFile;
use App\Enum\FileTypeEnum;
use App\Enum\ImportStatusEnum;
use App\Handler\ImportFileUploadHandler;
use App\Repository\ImportFileRepository;
use App\Tests\Factory\ImportFileFactory;
use App\Tests\Factory\UserFactory;
use Doctrine\ORM\EntityManagerInterface;
use LogicException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Vich\UploaderBundle\Storage\StorageInterface;

final class ImportFileUploadHandlerTest extends KernelTestCase
{
    private ImportFile $importFile;

    public function setUp(): void
    {
        $this->importFile = new ImportFile();
        $fileFactory = new ImportFileFactory();
        $userFactory = new UserFactory(); 
        $em = self::getContainer()->get(EntityManagerInterface::class);
        
        $this->importFile = $fileFactory->create('employees.csv', FileTypeEnum::CSV);
        $user = $userFactory->create();
        $this->importFile->setOwner($user);

        $em->persist($this->importFile);
    }    

    public function testHandlesFileUpload(): void
    {
        self::bootKernel();

        $importFileUploader = self::getContainer()->get(ImportFileUploadHandler::class);
        $repository = self::getContainer()->get(ImportFileRepository::class);
        $storage = self::getContainer()->get(StorageInterface::class);

        $tmp = sys_get_temp_dir() . '/' . uniqid() . '.xml';
        copy(__DIR__ . '/../../Fixtures/Files/test_to_upload.xml', $tmp);
        $this->importFile->setFile(new UploadedFile($tmp, 'test_to_upload.xml', null, null, true));
        
        $importFileUploader->handleUploadedFile($this->importFile);
        $importFile = $repository->findOneBy([], ['id' => 'DESC']);

        $this->assertSame(1, $repository->count([]));
        $this->assertFileExists($storage->resolvePath($this->importFile, 'file'));
        $this->assertSame(ImportStatusEnum::STATUS_UPLOADED, $importFile->status);
        $this->assertSame(FileTypeEnum::XML, $importFile->fileType);
    }

    public function testDeleteThrowsIfPathDoesNotExist(): void
    {
        $this->expectException(LogicException::class);
        $em = self::getContainer()->get(EntityManagerInterface::class);

        $importFileUploader = self::getContainer()->get(ImportFileUploadHandler::class);
        $em->persist($this->importFile);
        $em->flush();

        $importFileUploader->deleteUploadedFile($this->importFile);
    }

    public function testUploadThrowsIfPathDoesNotExist(): void
    {
        $this->expectException(FileNotFoundException::class);
        $em = self::getContainer()->get(EntityManagerInterface::class);

        $importFileUploader = self::getContainer()->get(ImportFileUploadHandler::class);
        $em->persist($this->importFile);
        $em->flush();

        $importFileUploader->handleUploadedFile($this->importFile);
    }

    public function testDeletesUploadedFile(): void
    {
        $importFileUploader = self::getContainer()->get(ImportFileUploadHandler::class);
        $repository = self::getContainer()->get(ImportFileRepository::class);

        $tmp = sys_get_temp_dir() . '/' . uniqid() . '.xml';
        copy(__DIR__ . '/../../Fixtures/Files/test_to_upload.xml', $tmp);
        $this->importFile->setFile(new UploadedFile($tmp, 'test_to_upload.xml', null, null, true));
        $importFileUploader->handleUploadedFile($this->importFile);

        $this->assertFileExists($this->importFile->file->getPathname());

        $importFileUploader->deleteUploadedFile($this->importFile);

        $this->assertFileDoesNotExist($this->importFile->file->getPathname());
        $this->assertSame(0, $repository->count([]));
    }

    public function tearDown(): void
    {
        self::bootKernel();
        $fs = self::getContainer()->get(Filesystem::class);

        if (null !== $this->importFile->file && $fs->exists($this->importFile->file->getPathname())) {
            $fs->remove($this->importFile->file->getPathname());
        }
    }
}