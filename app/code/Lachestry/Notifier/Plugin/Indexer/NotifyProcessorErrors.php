<?php
declare(strict_types=1);

namespace Lachestry\Notifier\Plugin\Indexer;

use Magento\Indexer\Model\Processor;
use Lachestry\Notifier\Model\ErrorHandler;
use Lachestry\Notifier\Model\Config;

class NotifyProcessorErrors
{
    /**
     * @param ErrorHandler $errorHandler
     * @param Config $config
     */
    public function __construct(
        protected readonly ErrorHandler $errorHandler,
        protected readonly Config $config
    ) {}

    /**
     * Intercept indexer reindexAll execution and send notification on error
     *
     * @param Processor $subject
     * @param callable $proceed
     * @return void
     */
    public function aroundReindexAll(
        Processor $subject,
        callable $proceed
    ) {
        if (!$this->config->isIndexerNotificationEnabled()) {
            return $proceed();
        }

        try {
            return $proceed();
        } catch (\Throwable $e) {
            $this->errorHandler->handleError($e, 'indexer', [
                'action' => 'reindexAll',
                'indexer' => 'all'
            ]);
            
            throw $e;
        }
    }

    /**
     * Intercept indexer reindexAllInvalid execution and send notification on error
     *
     * @param Processor $subject
     * @param callable $proceed
     * @return void
     */
    public function aroundReindexAllInvalid(
        Processor $subject,
        callable $proceed
    ) {
        if (!$this->config->isIndexerNotificationEnabled()) {
            return $proceed();
        }

        try {
            return $proceed();
        } catch (\Throwable $e) {
            $this->errorHandler->handleError($e, 'indexer', [
                'action' => 'reindexAllInvalid',
                'indexer' => 'invalid'
            ]);
            
            throw $e;
        }
    }
}
