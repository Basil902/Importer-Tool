<?php

namespace App\Service;

use App\Entity\FileImport;
use App\Enum\ImportStatusEnum;
use App\Handler\ImportUserHandler;
use App\Import\ImportErrorLogger;
use App\Import\Reader\Strategy\ReaderInterface;
use App\Import\UserMapper;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Exception\MissingColumnException;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\HttpKernel\Profiler\Profiler;
use Symfony\Contracts\Service\Attribute\Required;

final class ImporterService
{
    private ?Profiler $profiler = null;
    private ImportFileLocator $importFileLocator;
    private UserMapper $userMapper;
    private ImportErrorLogger $ImportErrorLogger;
    private const REQUIRED_COLUMNS = ['Name', 'Email', 'Role', 'isActive'];

    public function __construct(
        protected EntityManagerInterface $em,
        protected ImportUserHandler $importUserHandler,
        protected BatchService $batchService,
        #[AutowireIterator('app.reader_strategy')]
        private iterable $readers,
    )
    {
    }

    #[Required]
    public function setImportFileLocator(ImportFileLocator $importFileLocator): void
    {
        $this->importFileLocator = $importFileLocator;
    }

    #[Required]
    public function setUserMapper(UserMapper $userMapper): void
    {
        $this->userMapper = $userMapper;
    }

    #[Required]
    public function setImportErrorLogger(ImportErrorLogger $ImportErrorLogger): void
    {
        $this->ImportErrorLogger = $ImportErrorLogger;
    }

    #[Required]
    public function setProfiler(?Profiler $profiler): void
    {
        $this->profiler = $profiler;
    }

    public function execute(FileImport $file, string $fileType, int $fileImportId): void
    {
        $file = $this->importFileLocator->getFileToImport($file);
        $importReader = $this->readerFor($fileType);
        # disable profiler to reduce memory usage
        $this->disableProfiler();
        $this->updateFileImportStatus($fileImportId, ImportStatusEnum::STATUS_PROCESSING);

        try {
            foreach ($importReader->read($file) as $lineNo => $data) {

                # check if there are any missing headers.
                $missing = array_diff(self::REQUIRED_COLUMNS, array_keys($data));

                if ([] !== $missing) {
                    throw new MissingColumnException(implode(',', $missing));
                }

                # the inner try/catch is for logging purposes, in case some rows are malformed. It also doesnt stop the import flow.
                try {
                    $dto = $this->userMapper->mapDto($data);
                    $this->importUserHandler->handleUserData($dto);
                } catch (\Throwable $e) {
                    $this->ImportErrorLogger->log("Error while processing file with ID {$fileImportId}: row $lineNo: {$e->getMessage()}");
                }     
            }

            $this->batchService->finalize();
            $this->updateFileImportStatus($fileImportId, ImportStatusEnum::STATUS_PROCESSED);
            return;

        } catch (\Throwable $e) {
            $this->updateFileImportStatus($fileImportId, ImportStatusEnum::STATUS_ERROR);
            throw $e;
        }
         
        
    }

    protected function readerFor(string $fileType): ReaderInterface
    {
        foreach ($this->readers as $reader) {
            if ($reader->supports($fileType)) {
                return $reader;
            }
        }

        throw new \RuntimeException("No import reader found for type '{$fileType}'.");
    }

    public function updateFileImportStatus(int $importId, ImportStatusEnum $importStatus): void
    {
        $fileImport = $this->em->getRepository(FileImport::class)->find($importId);

        if (!$fileImport) {
            throw new \RuntimeException("No file import found with ID {$importId}.");
        }

        $fileImport->status = $importStatus;
        
        $this->em->flush();
    }

    private function disableProfiler(): void
    {
        if (null != $this->profiler) {
            $this->profiler->disable();
        }
    }
}