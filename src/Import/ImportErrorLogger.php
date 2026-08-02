<?php

namespace App\Import;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

final class ImportErrorLogger
{
    public function __construct(
        #[Autowire('%kernel.project_dir%')]
        private string $projectDir
    ) {
    }

    /**
     * Log an import-related message to a specific file.
     * 
     * @param string $message     The message that should be logged / written.
     */
    public function log(string $message) {

        $file = $this->projectDir . '/var/log/import_error.log';
        
        $handle = fopen($file, 'a');

        try {
            fwrite($handle, $message . '\n');
        } catch (\Exception $e) {
            throw new \RuntimeException("Exception while trying to write to import error log: {$e}");
        }
    }
}