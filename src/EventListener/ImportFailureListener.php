<?php

namespace App\EventListener;

use App\Enum\ImportStatusEnum;
use App\Import\ImportErrorLogger;
use App\Message\ImportFileMessage;
use App\Repository\ImportFileRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Messenger\Event\WorkerMessageFailedEvent;
use Symfony\Component\Messenger\Exception\HandlerFailedException;

#[AsEventListener]
final class ImportFailureListener
{
    public function __construct(
        private ImportFileRepository $importFileRepository,
        private EntityManagerInterface $em,
        protected ImportErrorLogger $logger
    )
    {
    }

    public function __invoke(WorkerMessageFailedEvent $event): void
    {
        $message = $event->getEnvelope()->getMessage();
        if (!$message instanceof ImportFileMessage) {
            return;
        }

        $throwable = $event->getThrowable();
        if ($throwable instanceof HandlerFailedException) {
            $throwable = $throwable->getPrevious();
        }

        $importFile = $this->importFileRepository->find($message->importFileId);
        $this->logger->log($throwable->getMessage());
        
        if (null !== $importFile) {
            $importFile->status = ImportStatusEnum::STATUS_ERROR;
        
            $this->em->flush();
        }
        
    }
}