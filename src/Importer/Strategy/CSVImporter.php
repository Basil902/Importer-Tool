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
        // $content = file_get_contents($file);
        // $rows = explode("\r\n", $content);

        // $this->extractData($rows);
    }

    protected function extractData(array $row): void
    {
        // foreach (array_slice($rows, 1) as $row) {

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
            // überlegen ob finalize im importer service aufgerufen wird oder doch hier?
            // $this->importUserHandler->finalize();
        // }
    }
}