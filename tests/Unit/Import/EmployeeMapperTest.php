<?php

namespace App\Tests\Unit\Import;

use App\Entity\ImportFile;
use App\Entity\User;
use App\Enum\FileTypeEnum;
use App\Import\EmployeeMapper;
use App\Tests\Factory\ImportFileFactory;
use PHPUnit\Framework\TestCase;

final class EmployeeMapperTest extends TestCase
{
    private ImportFile $importFile;

    public function setUp(): void
    {
        $importFileFactory = new ImportFileFactory();
        $user =  $this->createStub(User::class);

        $importFile = $importFileFactory->create("test.csv", FileTypeEnum::CSV);

        $importFile->setOwner($user);

        $this->importFile = $importFile;

        parent::setUp();
    }

    public function testMapsRowToDto(): void
    {
        $mapper = new EmployeeMapper();

        $employeeDTO = $mapper->mapDto([
            'Name' => 'Max Mustermann',
            'Email' => 'mmustermann@webmail.com',
            'Role' => 'Senior Dev',
            'isActive' => 'yes',
            ],
            $this->importFile
        );

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
            ],
            $this->importFile
        );
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
            ],
            $this->importFile
        );
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
            ],
            $this->importFile
        );
    }
}