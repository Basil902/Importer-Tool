<?php

namespace App\Tests\Unit\Handler;

use App\DTO\EmployeeDTO;
use App\Entity\Employee;
use App\Handler\ImportEmployeeHandler;
use App\Service\BatchService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

final class ImportEmployeeHandlerTest extends TestCase
{
    public function testPersistsEmployeeFromDto(): void
    {
        $dto = new EmployeeDTO();

        $dto->name = 'Max Mustermann';
        $dto->email = 'mmustermann@webmail.com';
        $dto->role = 'Senior Dev';
        $dto->isActive = 'yes';

        $em = $this->createMock(EntityManagerInterface::class);
        $em
            ->expects($this->once())
            ->method('persist')
            ->willReturnCallback(function ($entity) use (&$persisted) {
                $persisted = $entity;
            });

        $handler = new ImportEmployeeHandler($em, $this->createStub(BatchService::class));

        $handler->handleEmployeeData($dto);

        $this->assertInstanceOf(Employee::class, $persisted);
        $this->assertSame('Max Mustermann', $persisted->name);
        $this->assertSame('mmustermann@webmail.com', $persisted->email);
        $this->assertSame('Senior Dev', $persisted->role);
        $this->assertTrue($persisted->isActive);
    }

    public function testNotifiesBatchServiceAfterPersisting(): void
    {
        $dto = new EmployeeDTO();

        $dto->name = 'Max Mustermann';
        $dto->email = 'mmustermann@webmail.com';
        $dto->role = 'Senior Dev';
        $dto->isActive = 'yes';

        $em = $this->createStub(EntityManagerInterface::class);

        $batch = $this->createMock(BatchService::class);
        $batch
            ->expects($this->once())
            ->method('handleBatchClearCycle');

        $handler = new ImportEmployeeHandler($em, $batch);
        $handler->handleEmployeeData($dto);
    }

    private static function makeDto(string $name, string $email, string $role): EmployeeDTO
    {
        $dto = EmployeeDTO::create();

        $dto->name = $name;
        $dto->email = $email;
        $dto->role = $role;
        $dto->isActive = 'yes';

        return $dto;
    }

    public static function employeesToProcess(): array
    {
        return [
            self::makeDto('Max Mustermann', 'mmustermann@webmail.com', 'Senior Dev'),
            self::makeDto('Ana Musterfrau', 'amusterfrau@gmail.com', 'Project Manager'),
        ];
    }

    public function testNotifiesBatchServiceForEachProcessedEmployee(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $em
            ->expects($this->exactly(2))
            ->method('persist');

        $batch = $this->createMock(BatchService::class);
        $batch
            ->expects($this->exactly(2))
            ->method('handleBatchClearCycle');

        $handler = new ImportEmployeeHandler($em, $batch);
        
        foreach (self::employeesToProcess() as $employee) {
            $handler->handleEmployeeData($employee);
        }

        $this->assertSame(2, $batch->processed);
    }
}