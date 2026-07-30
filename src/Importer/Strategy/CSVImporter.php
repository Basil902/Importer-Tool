<?php

namespace App\Importer\Strategy;

use App\Entity\FileImport;
use App\Importer\AbstractImporter;
use Exception;

final class CSVImporter extends AbstractImporter
{
    public function supports(string $type): bool
    {
        return 'csv' === $type;
    }

    public function import(FileImport $file): void
    {
        $this->savedUserCount = 0;

        $file = $this->getFileToImport($file);

        $fileStream = fopen($file, 'r');

        // Skip the first row, which are just the columns (Name, Email, etc)
        $headers = fgetcsv($fileStream, separator: ';');

        while (($row = fgetcsv($fileStream, separator: ';')) !== false) {
            // skip if row is empty (can happen when working with csv)
            if (empty($row)) {
                continue;
            }

            $this->extractData($row);
        }

        fclose($fileStream);
    }

    protected function extractData(array $row): void
    {
        try {
            $userDTO = $this->userDTO::create();

            [$name, $email, $role, $isActive] = $row;

            $userDTO->name = $name;
            $userDTO->email = $userDTO->validateEmail($email);
            $userDTO->role = $role;
            $userDTO->isActive = $userDTO->normalizeBooleanValue($isActive);

            $this->importUserHandler->handleUserData($userDTO);

            ++ $this->savedUserCount;
        } catch (Exception $e) {
            throw new \RuntimeException("An Exception occurred while trying to extract data from import: {$e->getMessage()}");
            }
    }
}