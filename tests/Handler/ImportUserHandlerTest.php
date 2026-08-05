<?php

namespace App\Tests\Handler;

use App\DTO\UserDTO;
use App\Entity\User;
use App\Handler\ImportUserHandler;
use App\Service\BatchService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

final class ImportUserHandlerTest extends TestCase
{
    public function testPersistsUserFromDto(): void
    {
        $dto = new UserDTO();

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

        $handler = new ImportUserHandler($em, $this->createStub(BatchService::class));

        $handler->handleUserData($dto);

        $this->assertInstanceOf(User::class, $persisted);
        $this->assertSame('Max Mustermann', $persisted->name);
        $this->assertSame('mmustermann@webmail.com', $persisted->email);
        $this->assertSame('Senior Dev', $persisted->role);
        $this->assertTrue($persisted->isActive);
    }

    public function testNotifiesBatchServiceAfterPersisting(): void
    {
        $dto = new UserDTO();

        $dto->name = 'Max Mustermann';
        $dto->email = 'mmustermann@webmail.com';
        $dto->role = 'Senior Dev';
        $dto->isActive = 'yes';

        $em = $this->createStub(EntityManagerInterface::class);

        $batch = $this->createMock(BatchService::class);
        $batch
            ->expects($this->once())
            ->method('handleBatchClearCycle');

        $handler = new ImportUserHandler($em, $batch);
        $handler->handleUserData($dto);
    }

    private static function makeDto(string $name, string $email, string $role): UserDTO
    {
        $dto = UserDTO::create();

        $dto->name = $name;
        $dto->email = $email;
        $dto->role = $role;
        $dto->isActive = 'yes';

        return $dto;
    }

    public static function usersToProcess(): array
    {
        return [
            self::makeDto('Max Mustermann', 'mmustermann@webmail.com', 'Senior Dev'),
            self::makeDto('Ana Musterfrau', 'amusterfrau@gmail.com', 'Project Manager'),
        ];
    }

    public function testNotifiesBatchServiceForEachProcessedUser(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $em
            ->expects($this->exactly(2))
            ->method('persist');

        $batch = $this->createMock(BatchService::class);
        $batch
            ->expects($this->exactly(2))
            ->method('handleBatchClearCycle');

        $handler = new ImportUserHandler($em, $batch);
        
        foreach (self::usersToProcess() as $user) {
            $handler->handleUserData($user);
        }

        $this->assertSame(2, $batch->processed);
    }
}