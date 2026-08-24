<?php

namespace App\Handler;

use App\DTO\EmployeeDTO;

interface ImportEmployeeHandlerInterface
{
    public function handleEmployeeData(EmployeeDTO $employeeDTO): void;
}