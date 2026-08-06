<?php

namespace App\Import\Reader\Strategy;

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