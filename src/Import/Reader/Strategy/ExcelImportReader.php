<?php

namespace App\Import\Reader\Strategy;

use Generator;
use OpenSpout\Common\Exception\IOException;
use OpenSpout\Reader\XLSX\Reader as XLSXReader;
use App\Import\UnreadeableFileException;

final class ExcelImportReader implements ReaderInterface
{
    private $acceptedTypes = ['xlsx', 'xls', 'excel'];

    public function supports(string $type): bool
    {
        return in_array($type, $this->acceptedTypes, true);
    }

    public function read(string $file): Generator
    {
        $reader = new XLSXReader();

        try {
            $reader->open($file);
        } catch (IOException $e) {
            throw new UnreadeableFileException("Excel file '{$file}' is empty or malformed.");
        }
        

        try {
            foreach ($reader->getSheetIterator() as $sheet) {
                // reset header per sheet, for example when working with multiple sheets
                $header = null;
                foreach ($sheet->getRowIterator() as $index => $row) {

                    if (1 === $index) {
                        $header = $row->toArray();
                        continue;
                    }

                    /**
                     * Return an associative array, so the order of the Columns is irrelevant during an import.
                     * Access the value via the keys, which is more reliable than having a set order
                     */
                    yield $index => array_combine($header, $row->toArray());
                }
            }
        } finally {
            $reader->close();
        }
    }
}