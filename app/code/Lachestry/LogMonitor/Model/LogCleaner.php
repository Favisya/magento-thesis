<?php

declare(strict_types=1);

namespace Lachestry\LogMonitor\Model;

use Lachestry\LogMonitor\Model\ResourceModel\LogError\CollectionFactory;
use Magento\Framework\DB\TransactionFactory;
use Psr\Log\LoggerInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;

class LogCleaner
{
    private const LOGS_RETENTION_PERIOD = 90;
    
    private CollectionFactory $collectionFactory;
    private LoggerInterface $logger;
    private DateTime $dateTime;
    private TransactionFactory $transactionFactory;

    public function __construct(
        CollectionFactory $collectionFactory,
        LoggerInterface $logger,
        DateTime $dateTime,
        TransactionFactory $transactionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->logger = $logger;
        $this->dateTime = $dateTime;
        $this->transactionFactory = $transactionFactory;
    }

    public function cleanOldLogs(): int
    {
        try {
            $cutoffDate = $this->dateTime->gmtDate('Y-m-d H:i:s', time() - self::LOGS_RETENTION_PERIOD * 86400);
            
            $collection = $this->collectionFactory->create();
            $collection->addFieldToFilter('date', ['lt' => $cutoffDate]);
            
            $size = $collection->getSize();
            
            if ($size > 0) {
                $collection->walk('delete');
                $this->logger->info(sprintf('Cleaned %d old log records older than %s', $size, $cutoffDate));
            }
            
            return $size;
        } catch (\Exception $e) {
            $this->logger->error(
                'Failed to clean old logs: ' . $e->getMessage(),
                ['exception' => $e]
            );
            return 0;
        }
    }
}
