<?php

namespace App\Import\Reader\Strategy;

use App\Import\UnreadeableFileException;
use Generator;

final class CSVImportReader implements ReaderInterface
{
    public function supports(string $type): bool
    {
        return 'csv' === $type;
    }

    public function read(string $file): Generator
    {
        $fileStream = fopen($file, 'r');

        try {
            $headers = fgetcsv($fileStream, separator: ';', escape: '');

            if (0 === filesize($file) || false === $headers) {
                throw new UnreadeableFileException("CSV file '{$file}' is empty or malformed.");
            }

            while (($row = fgetcsv($fileStream, separator: ';', escape: '')) !== false) {
                // skip if row is empty
                if (empty($row)) {
                    continue;
                }

                yield array_combine($headers, $row);
            }
        } finally {
            fclose($fileStream);
        }
    }
}