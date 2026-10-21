<?php

namespace App\Import\Reader\Strategy;

use App\Import\UnreadeableFileException;
use Generator;
use JsonMachine\Exception\SyntaxErrorException;
use JsonMachine\Items;
use JsonMachine\JsonDecoder\ExtJsonDecoder;

final class JSONImportReader implements ReaderInterface
{
    public function supports(string $type): bool
    {
        return 'json' === $type;
    }

    public function read(string $file): Generator
    {        
        $fileName = basename($file);

        try {
            $rows = Items::fromFile($file, ['decoder' => new ExtJsonDecoder(true)]);

            if (false === $this->containsElements($rows)) {
                throw new UnreadeableFileException("JSON file '{$fileName}' is empty or malformed.");
            }

            foreach (Items::fromFile($file, ['decoder' => new ExtJsonDecoder(true)]) as $key => $value) {

                yield $key => $value;
            }

        } catch (SyntaxErrorException $e) {
            throw new UnreadeableFileException("JSON file '{$fileName}' is empty or malformed.");
        }
    }

    private function containsElements(Items $rows): bool
    {
        // returns true if the iterable contains at least one row
        foreach ($rows as $el) {
            return true;
        }

        return false;
    }
}