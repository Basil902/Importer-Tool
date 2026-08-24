<?php

namespace App\Tests\Unit\DTO;

use App\DTO\EmployeeDTO;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class EmployeeDtoTest extends TestCase
{
    public static function validInputProvider(): array
    {
        return [
            'yes' => ['yes'],
            'no' => ['no'],
            'string 1' => ['1'],
            'yes with spaces' => [' yes '],
            'decimal 1.0' => [1.0],
            'string 0' => ['0'],
        ];
    }
    
    #[DataProvider('validInputProvider')]
    public function testNormalizesBooleanValue(mixed $input): void
    {
        $employeeDTO = new EmployeeDTO();
        $expectedValues = [true, false];

        $value = $employeeDTO->normalizeBooleanValue($input);

        $this->assertContains($value, $expectedValues);
    }

    public static function invalidInputProvider(): array
    {
        return [
            'empty string' => [''],
            'maybe' => ['maybe'],
            'string 7' => ['7'],
            'null' => [null],
            'decimal 3.0' => [3.0],
            'decimal 0.0' => [0.0],
            'string 1.0' => ['1.0'],
            'string 0.0' => ['0.0'],
        ];
    }

    #[DataProvider('invalidInputProvider')]
    public function testThrowsIfInvalidInput(mixed $input): void
    {
        $this->expectException(InvalidArgumentException::class);

        $employeeDTO = new EmployeeDTO();
        $employeeDTO->normalizeBooleanValue($input);
    }

    public static function validEmailProvider(): array
    {
        return [
            'valid email' => ['david@gmail.com'],
            'valid with spaces' => [' david@gmail.com '],
            'email with subdomain' => ['david@sub.gmail.com'],
        ];
    }

    #[DataProvider('validEmailProvider')]
    public function testReturnsValidEmail(mixed $email): void
    {
        $employeeDTO = new EmployeeDTO();

        $result = $employeeDTO->validateEmail($email);

        $this->assertContains($result, ['david@gmail.com', 'david@sub.gmail.com']);
    }

    public static function invalidEmailProvider(): array
    {
        return [
            'null' => [null],
            'number' => ['2'],
            'empty string' => [''],
            'space inbetween' => ['david @gmail . com'],
            'no TLD' => ['david@gmail'],
            'no TLD2' => ['david@gmail.'],
            'no name' => ['@gmail.com'],
            'no domain' => ['david@'],
            'no domain but TLD' => ['david@.com'],
            'numbers' => [' 1@1.1290 '],
        ];
    }

    #[DataProvider('invalidEmailProvider')]
    public function testThrowsIfInvalidEmail(mixed $email): void
    {
        $this->expectException(InvalidArgumentException::class);

        $employeeDTO = new EmployeeDTO();
        $employeeDTO->validateEmail($email);
    }

}