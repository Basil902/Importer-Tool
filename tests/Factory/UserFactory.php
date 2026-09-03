<?php

namespace App\Tests\Factory;

use App\Entity\User;

final class UserFactory
{
    public function create(): User
    {
        $user = new User();
        $user->email = 'test@mail.com';
        $user->setPassword('password');

        return $user;
    }
}