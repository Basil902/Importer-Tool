<?php

namespace App\Tests\Unit\Import;

use App\Import\EmployeeMapper;
use PHPUnit\Framework\TestCase;

final class EmployeeMapperTest extends TestCase
{
    public function testMapsRowToDto(): void
    {
        $mapper = new EmployeeMapper();

        $employeeDTO = $mapper->mapDto([
            'Name' => 'Max Mustermann',
            'Email' => 'mmustermann@webmail.com',
            'Role' => 'Senior Dev',
            'isActive' => 'yes',
        ]);

        $this->assertSame('Max Mustermann', $employeeDTO->name);
        $this->assertSame('mmustermann@webmail.com', $employeeDTO->email);
        $this->assertSame('Senior Dev', $employeeDTO->role);
        $this->assertTrue($employeeDTO->isActive);
    }

    public function testThrowsWhenEmailKeyIsMissing(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $mapper = new EmployeeMapper();

        $mapper->mapDto([
            'Name' => 'Max Mustermann',
            'Role' => 'Senior Dev',
            'isActive' => 'yes',
        ]);
    }

    public function testThrowsWhenEmailIsInvalid(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $mapper = new EmployeeMapper();

        $mapper->mapDto([
            'Name' => 'Max Mustermann',
            'Email' => 'invalid email format',
            'Role' => 'Senior Dev',
            'isActive' => 'yes',
        ]);
    }

    public function testThrowsWhenIsActiveKeyIsUnrecognizeable(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $mapper = new EmployeeMapper();

        $mapper->mapDto([
            'Name' => 'Max Mustermann',
            'Email' => 'mmustermann@webmail.com',
            'Role' => 'Senior Dev',
            'isActive' => 'qjknwf',
        ]);
    }
}