<?php

namespace App\Import;

use App\DTO\EmployeeDTO;
use App\Entity\ImportFile;

final class EmployeeMapper
{
    public function mapDto(array $row, ImportFile $importFile): EmployeeDTO
    {
        $employeeDTO = EmployeeDTO::create();

        $employeeDTO->name = $row['Name'] ?? null;
        $employeeDTO->email = $employeeDTO->validateEmail($row['Email'] ?? null);
        $employeeDTO->role = $row['Role'] ?? null;
        $employeeDTO->isActive = $employeeDTO->normalizeBooleanValue($row['isActive'] ?? null);
        $employeeDTO->importFile = $importFile;

        return $employeeDTO;
    }    
}