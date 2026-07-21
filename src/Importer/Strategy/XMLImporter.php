<?php

namespace App\Importer\Strategy;

use App\Entity\FileImport;
use App\Importer\AbstractImporter;
use Exception;

final class XMLImporter extends AbstractImporter
{
    public function supports(string $type): bool
    {
        return $type === 'xml';
    }

    public function import(FileImport $file): void
    {
        $data = [];

        $file = $this->getFileToImport($file);

        $xml = simplexml_load_file($file);

        foreach ($xml->children() as $child) {
            $data[] = $child;
        }

        $this->extractData($data);
    }

    protected function extractData(array $nodes): void
    {
        $this->savedUserCount = 0;

        try {
            foreach ($nodes as $user) {

                $userDTO = $this->userDTO::create();

                // cast to string to avoid errors or bugs
                $email = (string) $user->Email;
                $isActive = (string) $user->isActive;    

                $userDTO->name = $user->Name;
                $userDTO->email = $userDTO->validateEmail($email);
                $userDTO->role = $user->Role;
                $userDTO->isActive = $userDTO->normalizeBooleanValue($isActive);

                $this->importUserHandler->handleUserData($userDTO);

                ++ $this->savedUserCount;
            }

        } catch (Exception $e) {
            throw new \RuntimeException("An exception occurred while trying to extract data from import: '{$e->getMessage()}'");
        }
    }
}