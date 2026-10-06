<?php

namespace App\Service;

class LogFileParser
{
    public function parseFile(string $file, int $fileId): ?string
    {
        if (!file_exists($file)) {
            throw new \RuntimeException("Could not find log file in path: {$file}");
        }

        $content = file_get_contents($file);

        if (!str_contains($content, $fileId)) {
            return null;
        }

        foreach (explode("\n", $content) as $row) {
            preg_match("/ID\s({$fileId}):/", $row, $matches);

            if ([] === $matches || false === $matches) {
                continue;
            }
            
            $id = (int) $matches[1] ?? false;
            $offset = strpos($row, ':');
            $message = trim(substr($row, $offset + 1));

            if (false !== $id && $id === $fileId) {
                return $message;
            }
        }

        return null;
    }
}