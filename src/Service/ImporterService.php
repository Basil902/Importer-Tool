<?php

namespace App\Service;

use App\Entity\FileImport;
use App\Handler\ImportFileHandler;
use App\Handler\ImportUserHandler;
use App\Importer\Strategy\ImporterInterface;
use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\HttpKernel\Profiler\Profiler;
use Symfony\Contracts\Service\Attribute\Required;

final class ImporterService
{
    protected array $importers = [];
    protected ImporterInterface $activeImporter;
    private ImportFileHandler $importFileHandler;
    private ?Profiler $profiler;

    public function __construct(
        protected EntityManagerInterface $em,
        protected ImportUserHandler $importUserHandler,
        protected BatchService $batchService,
        #[AutowireIterator('app.importer_strategy')]
        iterable $importers,
    )
    {
        foreach ($importers as $importer) {
            $this->importers[] = $importer;
        }
    }

    #[Required]
    public function setImportFileHandler(ImportFileHandler $importFileHandler): void
    {
        $this->importFileHandler = $importFileHandler;
    }

    #[Required]
    public function setProfiler(?Profiler $profiler): void
    {
        $this->$profiler = $profiler;
    }

    public function execute(FileImport $file, string $fileType): void
    {
        $this->disableProfiler();

        foreach ($this->importers as $importer) {
            if ($importer->supports($fileType)) {
                
                $this->activeImporter = $importer;
                $importer->import($file);
                $this->batchService->finalize();
                $this->importFileHandler::STATUS_PROCESSED; 
                return;
            }
        }

        throw new RuntimeException("No importer found for type '{$fileType}'.");
    }

    public function getSavedUserCount(): int
    {
        if ($this->activeImporter !== null) {
            return $this->activeImporter->getSavedUserCount();
        }

        throw new \RuntimeException('Exception while trying to return saved user count: no active importer set');
    }

    private function disableProfiler(): void
    {
        $this->profiler?->disable();
    }
}