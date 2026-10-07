<?php

namespace App\Service;

class LogFileParser
{
    public function parseFile(string $file, int $fileId): ?string
    {
        if (!file_exists($file)) {
            throw new \RuntimeException("Could not find log file in path: {$file}");
        }

        $handle = fopen($file, 'r');

        if (false === $handle) {
            throw new \RuntimeException("Failed to open file.");
        }

        while (($line = fgets($handle)) !== false) {
            $message = $this->parseLine($line, $fileId);

            if (null !== $message) {
                fclose($handle);
                return $message;
            }
        }

        fclose($handle);

        return null;
    }

    private function parseLine(string $line, int $fileId): ?string
    {
        preg_match("/ID\s({$fileId}):/", $line, $matches);

        if ([] === $matches) {
            return null;
        }
        
        $id = (int) $matches[1];
        $offset = strpos($line, ':');
        $message = trim(substr($line, $offset + 1));

        if ($id === $fileId) {
            return $message;
        }

        return null;
    }
}