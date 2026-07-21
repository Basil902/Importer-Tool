<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

// das extends abstractcontroller weg machen nachdem fertig mit debuggen
class BatchService extends AbstractController
{
    protected int $processed = 0;
    private int $batchSize = 500;

    public function __construct(
        protected EntityManagerInterface $em
    )
    {
    }

    protected function handleBatchClearCycle(): void
    {
        if ($this->processed % $this->batchSize !== 0) {
            return;
        }

        $this->flushAndClear();
    }

    protected function flushAndClear(): void
    {
        // debug lines to check memory usage

        $memory = sprintf(
        "verbraucht=%.2fMB available=%.2fMB peak=%.2fMB\n",
        memory_get_usage(false) / 1024 / 1024,
        memory_get_usage(true) / 1024 / 1024,
        memory_get_peak_usage(true) / 1024 / 1024
        );

        // debug lines to check memory usage
        $path = $this->getParameter('kernel.project_dir') . '/var/log/newlog.log';
        $file = fopen($path, 'a');
        fwrite($file, "BEFORE FLUSH AND CLEAR: {$memory}");

        $this->em->flush();
        $this->em->clear();
        gc_collect_cycles();

        // also debug lines to check memory
        $memory2 = sprintf(
        "verbraucht=%.2fMB available=%.2fMB peak=%.2fMB\n",
        memory_get_usage(false) / 1024 / 1024,
        memory_get_usage(true) / 1024 / 1024,
        memory_get_peak_usage(true) / 1024 / 1024
        );

        fwrite($file, "AFTER CLEAR: {$memory2}\n");
        fclose($file);
    }
    public function finalize(): void
    {
        $this->em->flush();
    }
}