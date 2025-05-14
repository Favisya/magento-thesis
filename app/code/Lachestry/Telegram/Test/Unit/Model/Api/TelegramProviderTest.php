<?php

namespace Lachestry\Telegram\Test\Unit\Model\Api;

use Lachestry\Telegram\Model\Api\Http\Client;
use Lachestry\Telegram\Model\Api\TelegramProvider;
use Lachestry\Telegram\Model\Config;
use Lachestry\Telegram\Model\TelegramChatProvider;
use Lachestry\Telegram\Model\TelegramChat;
use Magento\Framework\Serialize\Serializer\Json;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class TelegramProviderTest extends TestCase
{
    protected TelegramProvider $telegramProvider;
    protected Client|MockObject $client;
    protected Json|MockObject $jsonSerializer;
    protected LoggerInterface|MockObject $logger;
    protected Config|MockObject $config;
    protected TelegramChatProvider|MockObject $telegramChatProvider;

    protected function setUp(): void
    {
        $this->client = $this->createMock(Client::class);
        $this->jsonSerializer = $this->createMock(Json::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->config = $this->createMock(Config::class);
        $this->telegramChatProvider = $this->createMock(TelegramChatProvider::class);

        $this->config->method('getToken')->willReturn('test_token');
        $this->config->method('getSendMsgMethod')->willReturn('sendMessage');
        $this->config->method('getGetMsgMethod')->willReturn('getUpdates');
        $this->config->method('getBaseUrl')->willReturn('https://api.telegram.org/bot');

        $this->telegramProvider = new TelegramProvider(
            $this->client,
            $this->jsonSerializer,
            $this->logger,
            $this->config,
            $this->telegramChatProvider
        );
    }

    public function testSendMessage()
    {
        $message = 'Test message';
        $chatId = 12345;
        $expectedData = ['text' => $message, 'chat_id' => $chatId];
        $expectedUrl = 'https://api.telegram.org/bottest_token/sendMessage';
        $expectedJson = '{"text":"Test message","chat_id":12345}';
        $expectedResponse = ['ok' => true, 'result' => []];

        $this->jsonSerializer->expects($this->once())
            ->method('serialize')
            ->with($expectedData)
            ->willReturn($expectedJson);

        $this->client->expects($this->once())
            ->method('request')
            ->with($expectedUrl, 'POST', $expectedJson)
            ->willReturn($expectedResponse);

        $result = $this->telegramProvider->sendMessage($message, $chatId);
        
        $this->assertEquals($expectedResponse, $result);
    }

    public function testSendMessageToAllChats()
    {
        $message = 'Broadcast message';
        $chat1 = $this->createConfiguredMock(TelegramChat::class, [
            'getChatId' => 111,
            'getChatName' => 'Chat 1'
        ]);
        $chat2 = $this->createConfiguredMock(TelegramChat::class, [
            'getChatId' => 222,
            'getChatName' => 'Chat 2'
        ]);
        
        $this->telegramChatProvider->expects($this->once())
            ->method('getActiveChats')
            ->willReturn([$chat1, $chat2]);
            
        $this->jsonSerializer->expects($this->exactly(2))
            ->method('serialize')
            ->willReturnMap([
                [['text' => $message, 'chat_id' => 111], '{"text":"Broadcast message","chat_id":111}'],
                [['text' => $message, 'chat_id' => 222], '{"text":"Broadcast message","chat_id":222}']
            ]);
            
        $this->client->expects($this->exactly(2))
            ->method('request')
            ->withConsecutive(
                ['https://api.telegram.org/bottest_token/sendMessage', 'POST', '{"text":"Broadcast message","chat_id":111}'],
                ['https://api.telegram.org/bottest_token/sendMessage', 'POST', '{"text":"Broadcast message","chat_id":222}']
            )
            ->willReturn(['ok' => true]);
            
        $this->telegramProvider->sendMessageToAllChats($message);
    }
    
    public function testGetMessages()
    {
        $expectedUrl = 'https://api.telegram.org/bottest_token/getUpdates';
        $expectedResponse = [
            'ok' => true,
            'result' => [
                ['update_id' => 1, 'message' => ['text' => 'Hello']],
                ['update_id' => 2, 'message' => ['text' => 'World']]
            ]
        ];
        
        $this->client->expects($this->once())
            ->method('request')
            ->with($expectedUrl)
            ->willReturn($expectedResponse);
            
        $result = $this->telegramProvider->getMessages();
        
        $this->assertEquals($expectedResponse, $result);
    }
    
    public function testGetMessagesEmptyResponse()
    {
        $expectedUrl = 'https://api.telegram.org/bottest_token/getUpdates';
        
        $this->client->expects($this->once())
            ->method('request')
            ->with($expectedUrl)
            ->willReturn([]);
            
        $result = $this->telegramProvider->getMessages();
        
        $this->assertEquals([], $result);
    }
} 