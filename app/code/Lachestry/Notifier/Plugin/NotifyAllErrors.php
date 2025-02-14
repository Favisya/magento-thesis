<?php
declare(strict_types=1);

namespace Lachestry\Notifier\Plugin;

use Psr\Log\LoggerInterface;
use Lachestry\Telegram\Api\NotificationInterface;

class NotifyAllErrors
{
    public function __construct(
        protected readonly NotificationInterface $notificationProvider,
    ) {}

    public function aroundRrror(
        LoggerInterface $logger,
        callable $subject,
        string|\Stringable $message,
        array $context
    ): void {
        if ($message instanceof \Stringable) {
            $message = $message->__toString();
        }

        $finalMessage = json_encode([$message, $context], JSON_UNESCAPED_UNICODE);
        $this->notificationProvider->sendMessageToAllChats($finalMessage);
    }
}
