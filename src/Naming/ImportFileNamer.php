<?php

namespace App\Naming;

use Vich\UploaderBundle\Mapping\PropertyMapping;
use Override;
use Vich\UploaderBundle\Naming\NamerInterface;

class ImportFileNamer implements NamerInterface
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

        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

        $mimeType = $file->getMimeType();

        $extension = match ($mimeType) {
            'text/csv' => 'csv',
            'text/xml' => 'xml',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
            'application/json' => 'json',
            default => strtolower($file->getClientOriginalExtension()),
        };

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