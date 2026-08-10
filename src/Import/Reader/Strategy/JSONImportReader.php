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
        try {
            foreach (Items::fromFile($file, ['decoder' => new ExtJsonDecoder(true)]) as $key => $value) {

                yield $key => $value;
            }
        } catch (SyntaxErrorException $e) {
            throw new UnreadeableFileException("JSON file '{$file}' is empty or malformed.");
        }
    }
}