<?php

namespace App\Tests\Factory;

use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class UserFactory
{
    public function __construct(private UserPasswordHasherInterface $passwordHasher)
    {
    }

    public function create(): User
    {
        $user = new User();
        $user->email = 'testuser@mail.com';
        $user->setPassword($this->passwordHasher->hashPassword($user, 'password'));

        return $user;
    }
}