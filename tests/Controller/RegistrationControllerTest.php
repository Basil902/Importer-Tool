<?php

namespace App\Tests;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RegistrationControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private UserRepository $userRepository;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->client->followRedirects(true);
        $container = static::getContainer();
        $this->userRepository = $container->get(UserRepository::class);
    }

    public function testRegister(): void
    {
        $this->client->request('GET', '/register');
        $this->assertResponseIsSuccessful();

        $this->client->submitForm('Register', [
            'registration_form[email]' => 'testuser@gmail.com',
            'registration_form[plainPassword]' => 'password123',
        ]);

        $userCount = $this->userRepository->count([]);

        $this->assertResponseIsSuccessful();
        $this->assertSame(2, $userCount);
    }

    public function testDeniesDuplicateEmail(): void
    {
        $this->client->request('GET', '/register');
        $this->assertResponseIsSuccessful();

        $this->client->submitForm('Register', [
            'registration_form[email]' => 'test@mail.com',
            'registration_form[plainPassword]' => 'password123',
        ]);
        
        $this->assertResponseIsUnprocessable('There is already an account with this email');
    }

    public function testDeniesIfInvalidCSRFToken(): void
    {
        $this->client->request('GET', '/register');
        $this->assertResponseIsSuccessful();

        $this->client->submitForm('Register', [
            'registration_form[email]' => 'testuser@gmail.com',
            'registration_form[plainPassword]' => 'password123',
            'registration_form[_token]' => null,
        ]);

        $this->assertResponseIsUnprocessable('The CSRF token is invalid. Please try to resubmit the form.');
    }

    public function testLogsUserInAfterSuccessfulRegistrastion(): void
    {
        $this->client->request('GET', '/register');
        $this->assertResponseIsSuccessful();

        $this->client->submitForm('Register', [
            'registration_form[email]' => 'testuser@gmail.com',
            'registration_form[plainPassword]' => 'password123',
        ]);

        $location = $this->client->getCrawler()->getUri();

        $this->assertSame('http://localhost/', $location);
        $this->assertSelectorExists('a:contains("Logout")');
    }
}
