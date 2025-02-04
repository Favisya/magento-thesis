<?php

namespace Lachestry\Telegram\Api;

interface NotificationInterface
{
    public function sendMessage(string $data, int $destination): array;

    public function getMessages(): array;
}
