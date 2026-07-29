<?php

namespace App\Service;

use App\Entity\FileImport;
use App\Enum\ImportStatusEnum;
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
    private ?Profiler $profiler = null;

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
    public function setProfiler(?Profiler $profiler): void
    {
        $this->$profiler = $profiler;
    }

    public function execute(FileImport $file, string $fileType, int $fileImportId): void
    {
        $this->disableProfiler();

        foreach ($this->importers as $importer) {
            if ($importer->supports($fileType)) {
                
                $this->activeImporter = $importer;
                $importer->import($file);
                $this->batchService->finalize();
                $this->updateFileImportStatus($fileImportId);
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

    public function updateFileImportStatus(int $importId): void
    {
        $fileImport = $this->em->getRepository(FileImport::class)->find($importId);

        if (!$fileImport) {
            throw new \RuntimeException("No file import found with ID {$importId}.");
        }

        $fileImport->status = ImportStatusEnum::STATUS_PROCESSED;
        
        $this->em->flush();
    }

    private function disableProfiler(): void
    {
        if (null != $this->profiler) {
            $this->profiler->disable();
        }
    }
}