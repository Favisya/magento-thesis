<?php
declare(strict_types=1);

namespace Lachestry\Notifier\Model;

use Lachestry\Configuration\Model\Config;
use Lachestry\Telegram\Model\TelegramClient;
use Magento\Framework\Exception\LocalizedException;

class Notification
{
    private Config $config;
    private TelegramClient $telegramClient;

    public function __construct(
        Config $config,
        TelegramClient $telegramClient
    ) {
        $this->config = $config;
        $this->telegramClient = $telegramClient;
    }

    public function sendNotification(string $message): void
    {
        if (!$this->config->isEnabled()) {
            throw new LocalizedException(__('Notifications are disabled'));
        }

        $chatId = $this->config->getConfigValue('lachestry/notifications/chat_id');
        if (empty($chatId)) {
            throw new LocalizedException(__('Telegram chat ID is not configured'));
        }

        $result = $this->telegramClient->sendMessage($chatId, $message);
        if (!$result) {
            throw new LocalizedException(__('Failed to send notification'));
        }
    }
} 