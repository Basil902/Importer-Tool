<?php

namespace App\DTO;

final class EmployeeDTO
{
    public string $name;
    public string $email;
    public string $role;
    public bool $isActive;

    public static function create(): self
    {
        return new self();
    }

    // type is mixed, since incomming value can be of either type string or boolean
    public function normalizeBooleanValue(mixed $value): bool
    {
        // additional check since null resolves to false and empty string to true
        $invalidValues = [null, ''];

        if (in_array($value, $invalidValues, true)) {
            $result = null;
        } else {
            $result = filter_var($value, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);
        }

        if (null !== $result) {
            return $result;
        }

        throw new \InvalidArgumentException("The boolean value '{$value}' is invalid.");
    }

    public function validateEmail(?string $email): string
    {
        # variable to store and return the valid e-mail, since we need the incomming $email for the exception
        $validEmail = '';

        if (null !== $email) {
            $validEmail = filter_var(trim($email), FILTER_VALIDATE_EMAIL);
        }

        # throw on falsy values, return only valid emails
        return $validEmail ?: throw new \InvalidArgumentException("The e-mail format '{$email}' is not supported.");
    }
}