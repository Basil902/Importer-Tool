<?php

namespace App\Handler;

use App\DTO\UserDTO;

interface ImportUserHandlerInterface
{
    public function handleUserData(UserDTO $userDTO): void;
}