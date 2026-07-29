<?php

namespace App\Message;

use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('async')]
final class ImportFileMessage
{
    public function __construct(
        public int $importFileId,
        public string $importFileType
    )
    {
    }
}