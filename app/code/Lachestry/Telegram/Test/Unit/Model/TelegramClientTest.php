<?php
/**
 * Тест для клиента Telegram
 */
namespace Lachestry\Telegram\Test\Unit\Model;

use PHPUnit\Framework\TestCase;
use Lachestry\Telegram\Model\TelegramClient;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Serialize\Serializer\Json;
use Lachestry\Configuration\Model\Config;
use PHPUnit\Framework\MockObject\MockObject;
use Lachestry\Telegram\Model\Api\TelegramProvider;

/**
 * @covers \Lachestry\Telegram\Model\TelegramClient
 */
class TelegramClientTest extends TestCase
{
    /**
     * @var TelegramClient
     */
    protected TelegramClient $telegramClient;

    /**
     * @var Curl|\PHPUnit\Framework\MockObject\MockObject
     */
    protected MockObject|Curl $curl;

    /**
     * @var Json|\PHPUnit\Framework\MockObject\MockObject
     */
    protected Json|MockObject $json;

    /**
     * @var Config|MockObject
     */
    protected Config|MockObject $config;

    /**
     * @var TelegramProvider|MockObject
     */
    protected TelegramProvider|MockObject $provider;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->curl = $this->getMockBuilder(Curl::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->json = $this->getMockBuilder(Json::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->config = $this->createMock(Config::class);

        $this->provider = $this->createMock(TelegramProvider::class);

        $this->telegramClient = new TelegramClient($this->provider, $this->config);
    }

    /**
     * Тест отправки сообщения
     */
    public function testSendMessageSuccess()
    {
        $chatId = 123456;
        $message = 'Test message';

        $this->provider->expects($this->once())
            ->method('sendMessage')
            ->with($message, $chatId)
            ->willReturn(['ok' => true]);

        $result = $this->telegramClient->sendMessage((string)$chatId, $message);
        $this->assertTrue($result);
    }

    public function testSendMessageFailure()
    {
        $chatId = 123456;
        $message = 'Test message';

        $this->provider->expects($this->once())
            ->method('sendMessage')
            ->with($message, $chatId)
            ->willReturn(['ok' => false, 'error' => 'Invalid chat ID']);

        $result = $this->telegramClient->sendMessage((string)$chatId, $message);
        $this->assertFalse($result);
    }

    public function testSendMessageInvalidResponse()
    {
        $chatId = 123456;
        $message = 'Test message';

        $this->provider->expects($this->once())
            ->method('sendMessage')
            ->with($message, $chatId)
            ->willReturn(['some_other_key' => 'value']);

        $result = $this->telegramClient->sendMessage((string)$chatId, $message);
        $this->assertFalse($result);
    }

    public function testSendMessageEmptyResponse()
    {
        $chatId = 123456;
        $message = 'Test message';

        $this->provider->expects($this->once())
            ->method('sendMessage')
            ->with($message, $chatId)
            ->willReturn([]);

        $result = $this->telegramClient->sendMessage((string)$chatId, $message);
        $this->assertFalse($result);
    }

    public function testSendMessageNonArrayResponse()
    {
        $chatId = 123456;
        $message = 'Test message';

        $this->provider->expects($this->once())
            ->method('sendMessage')
            ->with($message, $chatId)
            ->willReturn([]);

        $result = $this->telegramClient->sendMessage((string)$chatId, $message);
        $this->assertFalse($result);
    }
}
