<?php

namespace App\Naming;

use Vich\UploaderBundle\Mapping\PropertyMapping;
use Override;
use Vich\UploaderBundle\Naming\NamerInterface;

// Eigener Namer für hochgeladende Dateien
class FileImportNamer implements NamerInterface
{
    private const array SUPPORTED_EXTENSIONS = [
        'csv',
        'xml',
        'json',
        'xlsx',
    ];

    #[Override]
    public function name(object $object, PropertyMapping $mapping): string
    {
        $file = $mapping->getFile($object);
        $extension = $file->guessExtension();

        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

        if ('txt' === $extension) {
            $extension = 'text/csv' === $file->getClientMimeType() ? 'csv' : $extension;
        }

        if (!in_array($extension, self::SUPPORTED_EXTENSIONS)) {
            throw new \RuntimeException("File type '{$extension}' not supported.");
        }

        return sprintf('%s_%s_%s.%s',
            $originalName,
            date('Ymd'),
            bin2hex(random_bytes(4)),
            strtolower($extension)
        );
    }
}