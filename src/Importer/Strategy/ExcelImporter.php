<?php

namespace App\Importer\Strategy;

use App\Entity\FileImport;
use App\Importer\AbstractImporter;
use Exception;
use OpenSpout\Reader\XLSX\Reader;

final class ExcelImporter extends AbstractImporter
{
    private $acceptedTypes = ['xlsx', 'xls'];

    public function supports(string $type): bool
    {
        return in_array($type, $this->acceptedTypes, true);
    }

    public function import(FileImport $file): void
    {
        $this->savedUserCount = 0;

        $file = $this->getFileToImport($file);

        $reader = new Reader();
        $reader->open($file);

        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $index => $row) {

                // skip the headers (columns like Name, Email, etc)
                if (1 === $index) {
                    continue;
                }

                $dataRow = $row->toArray();

                $this->extractData($dataRow);
            }
        }
        // $reader->setReadDataOnly(true);
        // $spreadSheet = $reader->load($file);
        // $sheet = $spreadSheet->getActiveSheet();

        // foreach ($sheet->getRowIterator() as $index => $row) {
        //     $dataRow = [];

        //     // getRowIterator() is 1-based and thus starts at 1 instead of the conventional 0 in standard php arrays
        //     // skip the first row which are just the headers / columns
        //     if (1 === $index) {
        //         continue;
        //     }

        //     foreach ($row->getCellIterator() as $cell) {
        //         $dataRow[] = $cell->getValue();
        //     }

        //     $this->extractData($dataRow);
        // }
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

        } catch (Exception $e) {
            throw new \RuntimeException("An Exception occurred while trying to extract data from import: {$e->getMessage()}");
        }
    }
}