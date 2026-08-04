<?php

namespace App\Import;

use App\DTO\UserDTO;

final class UserMapper
{
    public function mapDto(array $row): UserDTO
    {
        $userDTO = UserDTO::create();

        $userDTO->name = $row['Name'] ?? null;
        $userDTO->email = $userDTO->validateEmail($row['Email'] ?? null);
        $userDTO->role = $row['Role'] ?? null;
        $userDTO->isActive = $userDTO->normalizeBooleanValue($row['isActive'] ?? null);

        return $userDTO;
    }    
}