<?php

namespace App\Handler;

use App\DTO\UserDTO;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\BatchService;

final class ImportUserHandler extends BatchService
{
    public function __construct(
        protected EntityManagerInterface $em
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

        ++ $this->processed;

        unset($userDTO);

        $this->handleBatchClearCycle();
    }
}