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

    public function normalizeBooleanValue(string $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_BOOL);
    }

    public function validateEmail(string $email): string
    {
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $email;
        } else {
            throw new \InvalidArgumentException("The e-mail format '{$email}' is not supported.");
        }
    }
}