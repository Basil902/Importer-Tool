<?php

namespace App\Importer\Strategy;

use App\Entity\FileImport;
use App\Importer\AbstractImporter;
use Exception;

final class JSONImporter extends AbstractImporter
{
    public function supports(string $type): bool
    {
        return $type === 'json';     
    }

    public function import(FileImport $file): void
    {
        $file = $this->getFileToImport($file);
        $data = json_decode(
            file_get_contents($file),
            true
        );

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('Fehler beim Importieren der JSON Datei: ' . json_last_error_msg());
        }

        $this->extractData($data);
    }

    protected function extractData(array $data): void
    {
        $this->savedUserCount = 0;

        try {
            foreach ($data as $dataSet) {

                $userDTO = $this->userDTO::create();
                
                // $data is an associate array with named keys
                ['Name' => $name, 'Email' => $email, 'Role' => $role, 'isActive' => $isActive] = $dataSet;

                $userDTO->name = $name;
                $userDTO->email = $userDTO->validateEmail($email);
                $userDTO->role = $role;
                $userDTO->isActive = $userDTO->normalizeBooleanValue($isActive);

                $this->importUserHandler->handleUserData($userDTO);

                ++ $this->savedUserCount;
            }

        } catch (Exception $e) {
            throw new \RuntimeException("An Exception occurred while trying to extract data from import: {$e->getMessage()}");
        }
    }
}