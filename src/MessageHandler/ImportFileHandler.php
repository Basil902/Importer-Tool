<?php

namespace App\MessageHandler;

use App\Message\ImportFileMessage;
use App\Repository\ImportFileRepository;
use App\Service\ImporterService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler()]
final class ImportFileHandler
{
    public function __construct(
        protected ImportFileRepository $repository,
        protected ImporterService $importerService
    )
    {
    }

    public function __invoke(
        ImportFileMessage $message
    ): void
    {
        $file = $this->repository->find($message->importFileId);

        if (!$file) {
            throw new \RuntimeException("No file with id '{$message->importFileId}' and type '{$message->importFileType}' found.");
        }

        $this->importerService->execute($file, $message->importFileType);
    }
}