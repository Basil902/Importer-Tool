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
}
