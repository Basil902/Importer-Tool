<?php

namespace App\Tests\Tests\Controller;

use App\Entity\ImportFile;
use App\Repository\ImportFileRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Filesystem\Filesystem;
use Vich\UploaderBundle\Storage\StorageInterface;

class HomeControllerTest extends WebTestCase
{
    private ImportFile $importFile;

    public function testUploadFileSuccessful(): void
    {
        $client = static::createClient();
        $storage = self::getContainer()->get(StorageInterface::class);
        $repository = self::getContainer()->get(ImportFileRepository::class);
        # follow all redirects
        $client->followRedirects();
        $crawler = $client->request('GET', '/');
        $this->assertResponseIsSuccessful();
        $filePath = dirname(__DIR__).'/Fixtures/Files/test_to_upload.xml';
        
        $buttonCrawlerNode = $crawler->selectButton('Upload');
        $form = $buttonCrawlerNode->form();
        $form['upload_file_form[file][file]']->setValue($filePath);
        $client->submit($form);
        $this->assertSame(1, $repository->count([]));

        $importFile = $repository->findOneBy([], ['id' => 'DESC']);
        # set importFile in order to delete the uploaded file later in tearDown()
        $this->importFile = $importFile;

        $this->assertResponseIsSuccessful();
        $this->assertFileExists($storage->resolvePath($importFile, 'file'));
    }

    public function testRejectsInvalidUploadedFile(): void
    {
        $client = static::createClient();        
        $crawler = $client->request('GET', '/');

        $this->assertResponseIsSuccessful();

        $filePath = dirname(__DIR__).'/Fixtures/Files/test.jpg';
        $buttonCrawlerNode = $crawler->selectButton('Upload');
        $form = $buttonCrawlerNode->form();

        $form['upload_file_form[file][file]']->setValue($filePath);
        $client->submit($form);
        
        $this->assertResponseIsUnprocessable('File type "jpg" is not allowed.');
    }

    public function testEndToEndImportProcess(): void
    {
        $client = static::createClient();
        $client->followRedirects();
        $client->disableReboot();
        $fileRepository = self::getContainer()->get(ImportFileRepository::class);
        $userRepository  = self::getContainer()->get(UserRepository::class);
        $storage = self::getContainer()->get(StorageInterface::class);

        $crawler = $client->request('GET', '/');
        $this->assertResponseIsSuccessful();

        $filePath = dirname(__DIR__).'/Fixtures/Files/test_to_upload.xml';
        $buttonCrawlerNode = $crawler->selectButton('Upload');
        $form = $buttonCrawlerNode->form();

        $form['upload_file_form[file][file]']->setValue($filePath);
        $client->submit($form);
        $this->assertResponseIsSuccessful();

        $crawler = $client->reload();

        $importButtonCrawlerNode = $crawler->selectButton('Import');
        $importForm = $importButtonCrawlerNode->form();
        $client->submit($importForm);
        $this->assertResponseIsSuccessful();

        $importFile = $fileRepository->findOneBy([], ['id' => 'DESC']);
        $this->importFile = $importFile;

        $this->assertSame(1, $fileRepository->count([]));
        $this->assertSame('processed', $importFile->status->value);
        $this->assertSame(3, $userRepository->count([]));
        $this->assertFileExists($storage->resolvePath($importFile, 'file'));
        $this->assertSelectorTextContains('p', 'processed');
    }


    public function tearDown(): void
    {
        $fileSystem = self::getContainer()->get(Filesystem::class);

        if (isset($this->importFile)) {
            $path = $this->importFile->file->getPathname();
            $fileSystem->remove($path);
        }

        parent::tearDown();
    }
}
