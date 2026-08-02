<?php

namespace App\Import\Reader\Strategy;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.reader_strategy')]
interface ReaderInterface
{
    public function supports(string $type): bool;

    public function read(string $file): \Generator;
}