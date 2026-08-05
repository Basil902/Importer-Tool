<?php

namespace App\Handler;

use App\DTO\UserDTO;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\BatchService;

final class ImportUserHandler
{
    public function __construct(
        protected EntityManagerInterface $em,
        protected BatchService $batchService
    )
    {
    }

    public function handleUserData(UserDTO $userDTO): void
    {
        $user = new User();

        $user->name = $userDTO->name;
        $user->email = $userDTO->email;
        $user->role = $userDTO->role;
        $user->isActive = $userDTO->isActive;

        $this->em->persist($user);

        ++ $this->batchService->processed;

        unset($userDTO);

        $this->batchService->handleBatchClearCycle();
    }
}