<?php

namespace Lachestry\Telegram\Api;

interface NotificationInterface
{
    public function sendMessageToAllChats(string $msg): void;

    public function sendMessage(string $data, int $destination): array;

    public function getMessages(): array;
}
