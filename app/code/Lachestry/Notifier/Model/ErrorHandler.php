<?php

declare(strict_types=1);

namespace Lachestry\Notifier\Model;

use Lachestry\Telegram\Api\NotificationInterface;
use Psr\Log\LoggerInterface;
use Lachestry\Notifier\Model\Config;
use Lachestry\Notifier\Model\MessageFormatter;

/**
 * Handles errors and sends notifications to Telegram
 */
class ErrorHandler
{
    /**
     * @param LoggerInterface $logger
     * @param NotificationInterface $notificationProvider
     * @param Config $config
     * @param MessageFormatter $messageFormatter
     */
    public function __construct(
        protected readonly LoggerInterface       $logger,
        protected readonly NotificationInterface $notificationProvider,
        protected readonly Config                $config,
        protected readonly MessageFormatter      $messageFormatter
    ) {
    }

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
        string     $source,
        array      $additionalData = [],
    ): void {
        if (!$this->isNotificationEnabledForSource($source)) {
            return;
        }

        $context = array_merge($additionalData, [
            'source' => $source,
            'file'   => $exception->getFile(),
            'line'   => $exception->getLine(),
            'trace'  => $exception->getTraceAsString(),
        ]);

        $this->logger->error($exception->getMessage(), $context);
        $message = $this->messageFormatter->formatTelegramMessage($exception, $source, $additionalData);
        $this->notificationProvider->sendMessageToAllChats($message);
    }

    /**
     * Check if notification is enabled for given source
     *
     * @param string $source
     * @return bool
     */
    private function isNotificationEnabledForSource(string $source): bool
    {
        if (!$this->config->isEnabled()) {
            return false;
        }

        return match ($source) {
            'indexer'       => $this->config->isIndexerNotificationEnabled(),
            'cron'          => $this->config->isCronNotificationEnabled(),
            'message_queue' => $this->config->isQueueNotificationEnabled(),
            'rest_api'      => $this->config->isApiNotificationEnabled(),
            'stuck_cron'    => $this->config->isStuckCronNotificationEnabled(),
            default         => true
        };
    }
}
