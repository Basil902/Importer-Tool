<?php

namespace App\Handler;

use App\DTO\EmployeeDTO;
use App\Entity\Employee;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\BatchService;

final class ImportEmployeeHandler implements ImportEmployeeHandlerInterface
{
    public function __construct(
        protected EntityManagerInterface $em,
        protected BatchService $batchService
    )
    {
    }

    public function handleEmployeeData(EmployeeDTO $employeeDTO): void
    {
        $employee = new Employee();

        $employee->name = $employeeDTO->name;
        $employee->email = $employeeDTO->email;
        $employee->role = $employeeDTO->role;
        $employee->isActive = $employeeDTO->isActive;

        $this->em->persist($employee);

        ++ $this->batchService->processed;

        unset($employeeDTO);

        $this->batchService->handleBatchClearCycle();
    }
}