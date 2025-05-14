<?php
declare(strict_types=1);

namespace Lachestry\Cron\Model;

use Lachestry\Configuration\Model\Config;
use Lachestry\LogMonitor\Model\LogMonitor;
use Lachestry\ProcessMonitor\Model\ProcessMonitor;
use Lachestry\RabbitMQMonitor\Model\RabbitMQMonitor;
use Lachestry\Notifier\Model\Notification;
use Magento\Framework\Exception\LocalizedException;

class CronJob
{
    private Config $config;
    private LogMonitor $logMonitor;
    private ProcessMonitor $processMonitor;
    private RabbitMQMonitor $rabbitMQMonitor;
    private Notification $notification;

    public function __construct(
        Config $config,
        LogMonitor $logMonitor,
        ProcessMonitor $processMonitor,
        RabbitMQMonitor $rabbitMQMonitor,
        Notification $notification
    ) {
        $this->config = $config;
        $this->logMonitor = $logMonitor;
        $this->processMonitor = $processMonitor;
        $this->rabbitMQMonitor = $rabbitMQMonitor;
        $this->notification = $notification;
    }

    public function execute(): void
    {
        if (!$this->config->isEnabled()) {
            return;
        }

        $issues = [];

        // Проверка логов
        $logErrors = $this->logMonitor->checkLogForErrors();
        if (!empty($logErrors)) {
            $issues[] = 'Found errors in logs: ' . count($logErrors) . ' entries';
        }

        // Проверка процессов
        $criticalProcesses = ['php-fpm', 'nginx', 'mysql'];
        foreach ($criticalProcesses as $process) {
            if (!$this->processMonitor->checkProcess($process)) {
                $issues[] = "Process {$process} is not running";
            }
        }

        // Проверка RabbitMQ
        $queues = ['catalog_product_import', 'catalog_product_export'];
        foreach ($queues as $queue) {
            $status = $this->rabbitMQMonitor->checkQueueStatus($queue);
            if (isset($status['error'])) {
                $issues[] = "RabbitMQ queue {$queue} error: {$status['error']}";
            } elseif (isset($status['messages']) && $status['messages'] > 1000) {
                $issues[] = "RabbitMQ queue {$queue} has too many messages: {$status['messages']}";
            }
        }

        // Отправка уведомления если есть проблемы
        if (!empty($issues)) {
            $message = "System monitoring issues detected:\n" . implode("\n", $issues);
            $this->notification->sendNotification($message);
        }
    }
} 