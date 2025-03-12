<?php
declare(strict_types=1);

namespace Lachestry\Notifier\Model;

use Lachestry\Telegram\Api\NotificationInterface;
use Psr\Log\LoggerInterface;
use Lachestry\Notifier\Model\Config;

class ErrorHandler
{
    /**
     * @param LoggerInterface $logger
     * @param NotificationInterface $notificationProvider
     * @param Config $config
     */
    public function __construct(
        protected readonly LoggerInterface $logger,
        protected readonly NotificationInterface $notificationProvider,
        protected readonly Config $config
    ) {}

    /**
     * Handle error and send notification to Telegram
     *
     * @param \Throwable $exception Exception or error
     * @param string $source Error source (cron, indexer, message_queue, etc.)
     * @param array $additionalData Additional data for context
     * @return void
     */
    public function handleError(
        \Throwable $exception,
        string $source,
        array $additionalData = []
    ): void {
        if (!$this->isNotificationEnabledForSource($source)) {
            return;
        }

        $context = array_merge($additionalData, [
            'source' => $source,
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString()
        ]);

        $this->logger->error($exception->getMessage(), $context);
        $message = $this->formatTelegramMessage($exception, $source, $additionalData);
        $this->notificationProvider->sendMessageToAllChats($message);
    }

    private function isNotificationEnabledForSource(string $source): bool
    {
        if (!$this->config->isEnabled()) {
            return false;
        }

        return match($source) {
            'indexer' => $this->config->isIndexerNotificationEnabled(),
            'cron' => $this->config->isCronNotificationEnabled(),
            'message_queue' => $this->config->isQueueNotificationEnabled(),
            'rest_api' => $this->config->isApiNotificationEnabled(),
            default => true
        };
    }

    /**
     * Format message for Telegram
     *
     * @param \Throwable $exception Exception or error
     * @param string $source Error source
     * @param array $additionalData Additional data
     * @return string
     */
    private function formatTelegramMessage(
        \Throwable $exception,
        string $source,
        array $additionalData
    ): string {
        $title = match ($source) {
            'cron' => '*CRON JOB ERROR*',
            'indexer' => '*INDEXER ERROR*',
            'message_queue' => '*MESSAGE QUEUE ERROR*',
            'rest_api' => '*REST API ERROR*',
            default => '*SYSTEM ERROR*'
        };

        $message = "🔴 $title\n";
        $message .= "⏰ " . date('Y-m-d H:i:s') . "\n";
        $message .= "🔍 Source: $source\n";

        if ($source === 'cron' && isset($additionalData['job_code'])) {
            $message .= "📋 Job: {$additionalData['job_code']}\n";
            
            if (isset($additionalData['scheduled_at'])) {
                $message .= "📅 Scheduled at: {$additionalData['scheduled_at']}\n";
            }
        } elseif ($source === 'indexer' && isset($additionalData['indexer'])) {
            $message .= "📋 Indexer: {$additionalData['indexer']}\n";
            
            if (isset($additionalData['title'])) {
                $message .= "📝 Title: {$additionalData['title']}\n";
            }
        } elseif ($source === 'message_queue' && isset($additionalData['topic'])) {
            $message .= "📋 Topic: {$additionalData['topic']}\n";
            
            if (isset($additionalData['message_id'])) {
                $message .= "🆔 Message ID: {$additionalData['message_id']}\n";
            }
        } elseif ($source === 'rest_api' && isset($additionalData['http_code'])) {
            $message .= "📋 HTTP Code: {$additionalData['http_code']}\n";
        }

        $message .= "❗ Error: " . $exception->getMessage() . "\n";
        $message .= "⚠️ File: " . $exception->getFile() . " (line " . $exception->getLine() . ")\n";
        
        $trace = explode("\n", $exception->getTraceAsString());
        $traceShort = array_slice($trace, 0, 3);
        $message .= "🔍 Stack trace:\n```" . implode("\n", $traceShort) . "```\n";

        return $message;
    }
} 