<?php

namespace App\DTO;

final class UserDTO
{
    public string $name;
    public string $email;
    public string $role;
    public bool $isActive;

    public static function create(): self
    {
        return new self();
    }

    public function normalizeBooleanValue(mixed $value): bool
    {
        $result = filter_var($value, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);
        if (!null === $result) {
            return $result;
        }

        throw new \InvalidArgumentException("The boolean value '{$result}' is invalid.");
    }

    public function validateEmail(?string $email): string
    {
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $email;
        }
        
        throw new \InvalidArgumentException("The e-mail format '{$email}' is not supported.");
        
    }
}