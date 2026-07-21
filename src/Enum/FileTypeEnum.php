<?php

namespace App\Enum;

use InvalidArgumentException;

enum FileTypeEnum: string
{
    case CSV = 'csv';
    case JSON = 'json';
    case XML = 'xml';
    case EXCEL = 'excel';

    static function normalizeExtension(string $extension): FileTypeEnum
    {
        return match ($extension) {
            'csv' => self::CSV,
            'json' => self::JSON,
            'xml' => self::XML,
            'xlsx', 'xls' => self::EXCEL,
            default => throw new InvalidArgumentException(sprintf('Unknown extension "%s"', $extension)),
        };
    }

    // return the extension of a file type 
    static function extension(FileTypeEnum $type): string
    {
        return match ($type) {
            self::CSV => 'csv',
            self::JSON => 'json',
            self::XML => 'xml',
            self::EXCEL => 'xlsx',
            default => throw new \InvalidArgumentException(sprintf("The type %s is not supported.", $type))
        };
    }
}