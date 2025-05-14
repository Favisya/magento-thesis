<?php
declare(strict_types=1);

/**
 * Тест для модели уведомлений
 */
namespace Lachestry\Notifier\Test\Unit\Model;

use PHPUnit\Framework\TestCase;
use Lachestry\Notifier\Model\Notification;
use Lachestry\Configuration\Model\Config;
use Lachestry\Telegram\Model\TelegramClient;
use Magento\Framework\Exception\LocalizedException;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @covers \Lachestry\Notifier\Model\Notification
 */
class NotificationTest extends TestCase
{
    /**
     * @var Notification
     */
    protected Notification $notification;

    /**
     * @var Config|MockObject
     */
    protected Config|MockObject $config;

    /**
     * @var TelegramClient|MockObject
     */
    protected TelegramClient|MockObject $telegramClient;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->config = $this->createMock(Config::class);
        $this->telegramClient = $this->createMock(TelegramClient::class);
        
        $this->notification = new Notification(
            $this->config,
            $this->telegramClient
        );
    }

    /**
     * Тест отправки уведомления
     */
    public function testSendNotificationSuccess(): void
    {
        $message = 'Test notification';
        $chatId = '123456';

        $this->config->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);

        $this->config->expects($this->once())
            ->method('getConfigValue')
            ->with('lachestry/notifications/chat_id')
            ->willReturn($chatId);

        $this->telegramClient->expects($this->once())
            ->method('sendMessage')
            ->with($chatId, $message)
            ->willReturn(true);

        $this->notification->sendNotification($message);
    }

    public function testSendNotificationWhenDisabled(): void
    {
        $message = 'Test notification';

        $this->config->expects($this->once())
            ->method('isEnabled')
            ->willReturn(false);

        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('Notifications are disabled');

        $this->notification->sendNotification($message);
    }

    public function testSendNotificationWithoutChatId(): void
    {
        $message = 'Test notification';

        $this->config->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);

        $this->config->expects($this->once())
            ->method('getConfigValue')
            ->with('lachestry/notifications/chat_id')
            ->willReturn('');

        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('Telegram chat ID is not configured');

        $this->notification->sendNotification($message);
    }

    public function testSendNotificationFailure(): void
    {
        $message = 'Test notification';
        $chatId = '123456';

        $this->config->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);

        $this->config->expects($this->once())
            ->method('getConfigValue')
            ->with('lachestry/notifications/chat_id')
            ->willReturn($chatId);

        $this->telegramClient->expects($this->once())
            ->method('sendMessage')
            ->with($chatId, $message)
            ->willReturn(false);

        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('Failed to send notification');

        $this->notification->sendNotification($message);
    }
} 