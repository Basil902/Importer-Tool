<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class BatchService
{
    public int $processed = 0;
    private int $batchSize = 500;

    public function __construct(
        protected EntityManagerInterface $em,
        #[Autowire(service: 'monolog.logger.batch')]
        private LoggerInterface $batchLogger
    )
    {
    }

    public function handleBatchClearCycle(): void
    {
        if ($this->processed % $this->batchSize !== 0) {
            return;
        }

        $this->flushAndClear();
    }

    protected function flushAndClear(): void
    {
        // debug lines to check memory usage

        $this->batchLogger->debug('before flush / clear: ' . $this->getMemoryUsage());

        $this->em->flush();
        $this->em->clear();
        gc_collect_cycles();

        $this->batchLogger->debug('after flush / clear: ' . $this->getMemoryUsage());
    }

    public function finalize(): void
    {
        $this->em->flush();
    }

    private function getMemoryUsage(): string
    {
        return sprintf(
            "verbraucht=%.2fMB available=%.2fMB peak=%.2fMB\n",
            memory_get_usage(false) / 1024 / 1024,
            memory_get_usage(true) / 1024 / 1024,
            memory_get_peak_usage(true) / 1024 / 1024
            );
    }
}