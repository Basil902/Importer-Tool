<?php

namespace App\Tests;

use App\Tests\Factory\UserFactory;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class LoginControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->client->followRedirects(true);
        $container = static::getContainer();
        $em = $container->get(EntityManagerInterface::class);
        $userFactory = new UserFactory($container->get(UserPasswordHasherInterface::class));

        $user = $userFactory->create();

        $em->persist($user);
        $em->flush();
    }

    public function testLogin(): void
    {
        $this->client->request('GET', '/login');
        $this->assertResponseIsSuccessful();

        $this->client->submitForm('Sign in', [
            '_username' => 'testuser@mail.com',
            '_password' => 'password',
        ]);

        $currentUri = $this->client->getHistory()->current()->getUri();

        $this->assertResponseIsSuccessful();
        $this->assertSame('http://localhost/', $currentUri);
        $this->assertSelectorNotExists('.alert-danger');
    }

    public static function invalidEmailProvider(): array
    {
        return [
            'missing_at' => ['testmail.com'],
            'missing_domain' => ['test@mail'],
            'sql_injection' => ['\'OR 1=1'],
            'simple_string' => ['testmail'],
            'null' => [null]
        ];
    }

    #[DataProvider('invalidEmailProvider')]
    public function testDeniesIfInvalidEmail(?string $email): void
    {
        $this->client->request('GET', '/login');
        $this->assertResponseIsSuccessful();

        $this->client->submitForm('Sign in', [
            '_username' => $email,
            '_password' => 'password',
        ]);

        $this->assertSelectorTextContains('.alert-danger', 'Invalid credentials.');
    }

    public static function invalidPasswordProvider(): array
    {
        return [
            'invalid_password' => ['invalid123'],
            'sql_injection' => ['\'OR 1=1'],
            'null' => [null],
        ];
    }

    #[DataProvider('invalidPasswordProvider')]
    public function testDeniesIfInvalidPassword(?string $password): void
    {
        $this->client->request('GET', '/login');
        $this->assertResponseIsSuccessful();

        $this->client->submitForm('Sign in', [
            '_username' => 'email@example.com',
            '_password' => $password,
        ]);

        $this->assertSelectorTextContains('.alert-danger', 'Invalid credentials.');
    }
}
