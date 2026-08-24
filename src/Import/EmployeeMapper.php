<?php

namespace App\Import;

use App\DTO\EmployeeDTO;

final class EmployeeMapper
{
    public function mapDto(array $row): EmployeeDTO
    {
        $employeeDTO = EmployeeDTO::create();

        $employeeDTO->name = $row['Name'] ?? null;
        $employeeDTO->email = $employeeDTO->validateEmail($row['Email'] ?? null);
        $employeeDTO->role = $row['Role'] ?? null;
        $employeeDTO->isActive = $employeeDTO->normalizeBooleanValue($row['isActive'] ?? null);

        return $employeeDTO;
    }    
}