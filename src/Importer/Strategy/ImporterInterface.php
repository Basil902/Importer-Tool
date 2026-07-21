<?php

namespace App\Importer\Strategy;

use App\Entity\FileImport;

interface ImporterInterface
{
    public function supports(string $type): bool;
    
    public function import(FileImport $file): void;

    public function getSavedUserCount(): int;
}