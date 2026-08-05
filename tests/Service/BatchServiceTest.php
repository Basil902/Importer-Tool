<?php

namespace App\Tests\Service;

use App\Service\BatchService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

final class BatchServiceTest extends TestCase
{
    public static function processedCounts(): iterable
    {
        return [
            [499, 0],
            [500, 1],
            [501, 0],
            [1000, 1]
        ];
    }

    #[DataProvider('processedCounts')]
    public function testClearsEntityManagerWhenBatchSizeReached(int $processed, int $expectedCalls): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->exactly($expectedCalls))->method('flush');
        $em->expects($this->exactly($expectedCalls))->method('clear');

        # new NulllLogger() instead of an actual LoggerInterface, because the logging part is not the main focus of this test.
        $batchService = new BatchService($em, new NullLogger());
        $batchService->processed = $processed;

        $batchService->handleBatchClearCycle();
    }
}