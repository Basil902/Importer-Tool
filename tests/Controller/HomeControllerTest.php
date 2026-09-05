<?php

namespace App\Tests\Tests\Controller;

use App\Entity\ImportFile;
use App\Entity\User;
use App\Repository\ImportFileRepository;
use App\Repository\EmployeeRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Filesystem\Filesystem;
use Vich\UploaderBundle\Storage\StorageInterface;

class HomeControllerTest extends WebTestCase
{
    private ImportFile $importFile;
    private KernelBrowser $client;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();
        $this->user = self::getContainer()->get(UserRepository::class)->findOneBy(['email' => 'test@mail.com']);
    }

    public function testUploadFileSuccessful(): void
    {
        $storage = self::getContainer()->get(StorageInterface::class);
        $fileRepository = self::getContainer()->get(ImportFileRepository::class);
        $filePath = dirname(__DIR__).'/Fixtures/Files/test_to_upload.xml';
        # follow all redirects
        $this->client->followRedirects();

        $this->client->loginUser($this->user);
        $crawler = $this->client->request('GET', '/');
        $this->assertResponseIsSuccessful();
        
        $buttonCrawlerNode = $crawler->selectButton('Upload');
        $form = $buttonCrawlerNode->form();
        $form['upload_file_form[file][file]']->setValue($filePath);
        $this->client->submit($form);
        $this->assertSame(1, $fileRepository->count([]));

        $importFile = $fileRepository->findOneBy([], ['id' => 'DESC']);
        # set importFile in order to delete the uploaded file later in tearDown()
        $this->importFile = $importFile;

        $this->assertResponseIsSuccessful();
        $this->assertFileExists($storage->resolvePath($importFile, 'file'));
    }

    public function testRejectsInvalidUploadedFile(): void
    {
        $this->client->loginUser($this->user);  
        $crawler = $this->client->request('GET', '/');

        $this->assertResponseIsSuccessful();

        $filePath = dirname(__DIR__).'/Fixtures/Files/test.jpg';
        $buttonCrawlerNode = $crawler->selectButton('Upload');
        $form = $buttonCrawlerNode->form();

        $form['upload_file_form[file][file]']->setValue($filePath);
        $this->client->submit($form);
        
        $this->assertResponseIsUnprocessable('File type "jpg" is not allowed.');
    }

    public function testEndToEndImportProcess(): void
    {
        $this->client->followRedirects();
        $this->client->disableReboot();
        $fileRepository = self::getContainer()->get(ImportFileRepository::class);
        $employeeRepository  = self::getContainer()->get(EmployeeRepository::class);
        $storage = self::getContainer()->get(StorageInterface::class);
        $filePath = dirname(__DIR__).'/Fixtures/Files/test_to_upload.xml';

        $this->client->loginUser($this->user);
        $crawler = $this->client->request('GET', '/');
        $this->assertResponseIsSuccessful();

        $buttonCrawlerNode = $crawler->selectButton('Upload');
        $form = $buttonCrawlerNode->form();
        $form['upload_file_form[file][file]']->setValue($filePath);
        $this->client->submit($form);
        $this->assertResponseIsSuccessful();

        $crawler = $this->client->reload();

        $importButtonCrawlerNode = $crawler->selectButton('Import');
        $importForm = $importButtonCrawlerNode->form();
        $this->client->submit($importForm);
        $this->assertResponseIsSuccessful();

        $importFile = $fileRepository->findOneBy([], ['id' => 'DESC']);
        $this->importFile = $importFile;

        $this->assertSame(1, $fileRepository->count([]));
        $this->assertSame('processed', $importFile->status->value);
        $this->assertSame(3, $employeeRepository->count([]));
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
