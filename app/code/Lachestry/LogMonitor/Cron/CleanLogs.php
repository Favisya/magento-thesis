<?php

declare(strict_types=1);

namespace Lachestry\LogMonitor\Cron;

use Lachestry\LogMonitor\Model\LogCleaner;
use Psr\Log\LoggerInterface;

class CleanLogs
{
    private LogCleaner $logCleaner;
    private LoggerInterface $logger;

    public function __construct(
        LogCleaner $logCleaner,
        LoggerInterface $logger
    ) {
        $this->logCleaner = $logCleaner;
        $this->logger = $logger;
    }

    public function execute(): void
    {
        try {
            $deletedCount = $this->logCleaner->cleanOldLogs();
            $this->logger->info(sprintf('Log monitor cleaned %d old error(s)', $deletedCount));
        } catch (\Exception $e) {
            $this->logger->error(
                'Failed to clean old logs: ' . $e->getMessage(),
                ['exception' => $e]
            );
        }
    }
}
