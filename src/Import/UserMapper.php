<?php

namespace App\Import;

use App\DTO\UserDTO;
use Exception;

final class UserMapper
{
    public function __construct(
        protected UserDTO $userDTO,
    )
    {
    }

    public function mapDto(array $row): UserDTO
    {
        try {
            $userDTO = $this->userDTO::create();

            $userDTO->name = $row['Name'];
            $userDTO->email = $userDTO->validateEmail($row['Email']);
            $userDTO->role = $row['Role'];
            $userDTO->isActive = $userDTO->normalizeBooleanValue($row['isActive']);

            return $userDTO;

        } catch (Exception $e) {
            throw new \RuntimeException("An Exception occurred while trying to extract data from import: {$e->getMessage()}");
        }
    }    
}