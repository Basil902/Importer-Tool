<?php

use App\Import\UserMapper;
use PHPUnit\Framework\TestCase;

final class UserMapperTest extends TestCase
{
    public function testMapsRowToDto(): void
    {
        $mapper = new UserMapper();

        $userDto = $mapper->mapDto([
            'Name' => 'Max Mustermann',
            'Email' => 'mmustermann@webmail.com',
            'Role' => 'Senior Dev',
            'isActive' => 'yes',
        ]);

        $this->assertSame('Max Mustermann', $userDto->name);
        $this->assertSame('mmustermann@webmail.com', $userDto->email);
        $this->assertSame('Senior Dev', $userDto->role);
        $this->assertTrue($userDto->isActive);
    }

    public function testThrowsWhenEmailKeyIsMissing(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $mapper = new UserMapper();

        $mapper->mapDto([
            'Name' => 'Max Mustermann',
            'Role' => 'Senior Dev',
            'isActive' => 'yes',
        ]);
    }

    public function testThrowsWhenEmailIsInvalid(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $mapper = new UserMapper();

        $mapper->mapDto([
            'Name' => 'Max Mustermann',
            'Email' => 'invalid email format',
            'Role' => 'Senior Dev',
            'isActive' => 'yes',
        ]);
    }

    public function testThrowsWhenIsActiveKeyIsUnrecognizeable(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $mapper = new UserMapper();

        $mapper->mapDto([
            'Name' => 'Max Mustermann',
            'Email' => 'mmustermann@webmail.com',
            'Role' => 'Senior Dev',
            'isActive' => 'qjknwf',
        ]);
    }
}