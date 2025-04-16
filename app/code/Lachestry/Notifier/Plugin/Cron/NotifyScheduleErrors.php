<?php

declare(strict_types=1);

namespace Lachestry\Notifier\Plugin\Cron;

use Magento\Cron\Model\Schedule;
use Lachestry\Notifier\Model\ErrorHandler;
use Lachestry\Notifier\Model\Config;

/**
 * Plugin to catch and notify about cron schedule errors
 */
class NotifyScheduleErrors
{
    /**
     * @param ErrorHandler $errorHandler
     * @param Config $config
     */
    public function __construct(
        protected readonly ErrorHandler $errorHandler,
        protected readonly Config $config
    ) {
    }

    /**
     * Track errors when schedule is being processed
     *
     * @param Schedule $subject
     * @param callable $proceed
     * @param string $status
     * @return Schedule
     */
    public function aroundSetStatus(
        Schedule $subject,
        callable $proceed,
        string $status
    ) {
        $result = $proceed($status);
        
        if ($status === Schedule::STATUS_ERROR && $this->config->isCronNotificationEnabled()) {
            try {
                $jobCode = $subject->getJobCode();
                $scheduledAt = $subject->getScheduledAt();
                $message = $subject->getMessages() ?: 'Unknown error';
                
                $exception = new \Exception($message);
                $this->errorHandler->handleError($exception, 'cron', [
                    'job_code'     => $jobCode,
                    'scheduled_at' => $scheduledAt,
                    'status'       => $status
                ]);
            } catch (\Throwable $e) {
                // Do nothing to avoid interrupting the main process
            }
        }
        
        return $result;
    }
}
