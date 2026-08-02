<?php

namespace App\Import\Reader\Strategy;

use Generator;
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
        foreach (Items::fromFile($file, ['decoder' => new ExtJsonDecoder(true)]) as $key => $value) {
            yield $key => $value;
        }
    }
}