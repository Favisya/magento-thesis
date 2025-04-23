<?php

declare(strict_types=1);

namespace Lachestry\LogMonitor\Model;

use Lachestry\LogMonitor\Api\LogErrorRepositoryInterface;
use Lachestry\LogMonitor\Model\LogErrorFactory;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem\Driver\File;
use Psr\Log\LoggerInterface;

class LogParser
{
    private const LOG_DIR_PATH = 'var/log/';
    private const LOG_FILES = [
        'system.log',
        'exception.log',
        'debug.log',
        'cron.log',
        'telegram.api.log',
    ];

    private const SEVERITY_MAP = [
        'DEBUG'     => 'DEBUG',
        'INFO'      => 'INFO',
        'NOTICE'    => 'NOTICE',
        'WARNING'   => 'WARNING',
        'ERROR'     => 'ERROR',
        'CRITICAL'  => 'CRITICAL',
        'ALERT'     => 'ALERT',
        'EMERGENCY' => 'EMERGENCY',
    ];

    private File $fileDriver;
    private LogErrorFactory $logErrorFactory;
    private LogErrorRepositoryInterface $logErrorRepository;
    private LoggerInterface $logger;

    public function __construct(
        File                        $fileDriver,
        LogErrorFactory             $logErrorFactory,
        LogErrorRepositoryInterface $logErrorRepository,
        LoggerInterface             $logger,
    ) {
        $this->fileDriver         = $fileDriver;
        $this->logErrorFactory    = $logErrorFactory;
        $this->logErrorRepository = $logErrorRepository;
        $this->logger             = $logger;
    }

    public function parse(): int
    {
        $totalParsed = 0;

        foreach (self::LOG_FILES as $logFileName) {
            $totalParsed += $this->parseLogFile($logFileName);
        }

        return $totalParsed;
    }

    private function parseLogFile(string $logFileName): int
    {
        $parsedCount = 0;
        $logFilePath = BP . '/' . self::LOG_DIR_PATH . $logFileName;

        try {
            if (!$this->fileDriver->isExists($logFilePath)) {
                return 0;
            }

            $logContent = $this->fileDriver->fileGetContents($logFilePath);
            $lines      = explode("\n", $logContent);

            foreach ($lines as $line) {
                if (empty(trim($line))) {
                    continue;
                }

                if ($this->processLogLine($line, $logFileName)) {
                    $parsedCount++;
                }
            }
        } catch (FileSystemException $e) {
            $this->logger->error(
                'Failed to parse log file: ' . $logFileName . '. Error: ' . $e->getMessage()
            );
        }

        return $parsedCount;
    }

    private function processLogLine(string $line, string $logFileName): bool
    {
        $regex = '/^\[([^\]]+)\] ([^:]+): (.*)/';
        if (!preg_match($regex, $line, $matches)) {
            return false;
        }

        if (count($matches) < 4) {
            return false;
        }

        $dateTime    = $matches[1];
        $severityRaw = explode('.', strtoupper($matches[2]));
        $severityRaw = end($severityRaw);
        $message     = $matches[3];

        $severity = self::SEVERITY_MAP[$severityRaw] ?? 'info';

        try {
            $logError = $this->logErrorFactory->create();
            $logError->setLogFile($logFileName);
            $logError->setDate($dateTime);
            $logError->setSeverity($severity);
            $logError->setMessage($message);
            $logError->setContext('');

            $this->logErrorRepository->save($logError);
            return true;
        } catch (\Exception $e) {
            $this->logger->error(
                'Failed to save log error: ' . $e->getMessage(),
                ['line' => $line, 'file' => $logFileName]
            );
            return false;
        }
    }
}
