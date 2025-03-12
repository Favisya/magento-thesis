<?php
declare(strict_types=1);

namespace Lachestry\Notifier\Plugin\Cron;

use Magento\Cron\Observer\ProcessCronQueueObserver;
use Magento\Framework\Event\Observer;
use Lachestry\Notifier\Model\ErrorHandler;
use Lachestry\Notifier\Model\Config;

class NotifyObserverErrors
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
     * Intercept cron job execution to catch errors
     *
     * @param ProcessCronQueueObserver $subject
     * @param callable $proceed
     * @param Observer $observer
     * @return mixed
     */
    public function aroundExecute(
        ProcessCronQueueObserver $subject,
        callable $proceed,
        Observer $observer
    ) {
        if (!$this->config->isCronNotificationEnabled()) {
            return $proceed($observer);
        }

        try {
            return $proceed($observer);
        } catch (\Throwable $exception) {
            try {
                $this->errorHandler->handleError($exception, 'cron', [
                    'observer' => get_class($observer),
                    'action' => 'execute'
                ]);
            } catch (\Throwable $e) {
                // Do nothing to avoid interrupting the main process
            }
            
            throw $exception;
        }
    }
} 