<?php
declare(strict_types=1);

namespace Lachestry\Telegram\Model;

use Lachestry\Telegram\Model\Api\TelegramProvider;
use Lachestry\Configuration\Model\Config;

class TelegramClient
{
    /**
     * @param TelegramProvider $provider
     * @param Config $config
     */
    public function __construct(
        private readonly TelegramProvider $provider,
        private readonly Config $config
    ) {
    }

    /**
     * Отправляет сообщение в Telegram
     *
     * @param string $chatId
     * @param string $message
     * @return bool
     */
    public function sendMessage(string $chatId, string $message): bool
    {
        $result = $this->provider->sendMessage($message, (int)$chatId);
        return is_array($result) && isset($result['ok']) && $result['ok'] === true;
    }
} 