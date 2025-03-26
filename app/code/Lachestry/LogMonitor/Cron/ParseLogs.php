<?php
declare(strict_types=1);

namespace Lachestry\LogMonitor\Cron;

use Lachestry\LogMonitor\Model\LogParser;
use Psr\Log\LoggerInterface;

class ParseLogs
{
    private LogParser $logParser;
    private LoggerInterface $logger;

    public function __construct(
        LogParser $logParser,
        LoggerInterface $logger
    ) {
        $this->logParser = $logParser;
        $this->logger = $logger;
    }

    public function execute(): void
    {
        try {
            $parsedCount = $this->logParser->parse();
            $this->logger->info(sprintf('Log monitor parsed %d error(s)', $parsedCount));
        } catch (\Exception $e) {
            $this->logger->error(
                'Failed to parse logs: ' . $e->getMessage(),
                ['exception' => $e]
            );
        }
    }
} 