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
            if (null === Items::fromFile($file, ['decoder' => new ExtJsonDecoder(true)])) {
                throw new UnreadeableFileException("JSON file '{$fileName}' is empty or malformed.");
            }

            foreach (Items::fromFile($file, ['decoder' => new ExtJsonDecoder(true)]) as $key => $value) {

                yield $key => $value;
            }
        } catch (SyntaxErrorException $e) {
            throw new UnreadeableFileException("JSON file '{$fileName}' is empty or malformed.");
        }
    }
}