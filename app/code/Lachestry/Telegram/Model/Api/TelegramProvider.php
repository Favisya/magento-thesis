<?php
declare(strict_types=1);

namespace Lachestry\Telegram\Model\Api;

use Lachestry\Telegram\Api\NotificationInterface;
use Lachestry\Telegram\Model\Api\Http\Client;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Setup\Exception;
use Psr\Log\LoggerInterface;

class TelegramProvider implements NotificationInterface
{
    public const TELEGRAM   = 'telegram';
    public const TOKEN      = 'token';
    public const TEXT       = 'text';
    public const BASE_PATH  = 'lachestry_telegram/general/';
    public const SEND_MSG   = 'send_method';
    public const GET_MSG    = 'get_method';
    public const BASE_URL   = 'base_url';

    protected string $url;
    protected mixed $token;
    protected Client $client;
    protected mixed $baseUrl;
    protected mixed $getMsgMethod;
    protected mixed $sendMsgMethod;
    protected LoggerInterface $logger;
    protected Json $jsonSerializer;

    public function __construct(
        Client $client,
        Json $jsonSerializer,
        LoggerInterface $logger,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->client         = $client;
        $this->logger         = $logger;
        $this->jsonSerializer = $jsonSerializer;

        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;

        $this->token         = $scopeConfig->getValue(self::BASE_PATH . self::TOKEN, $storeScope);
        $this->sendMsgMethod = $scopeConfig->getValue(self::BASE_PATH . self::SEND_MSG, $storeScope);
        $this->getMsgMethod  = $scopeConfig->getValue(self::BASE_PATH . self::GET_MSG, $storeScope);
        $this->baseUrl       = $scopeConfig->getValue(self::BASE_PATH . self::BASE_URL, $storeScope);

        $this->url = $this->baseUrl . $this->token . '/';
    }

    public function sendMessage(string $data, int $destination): array
    {
        $data = $this->prepareData($data, $destination);
        $url = $this->prepareUrl($this->sendMsgMethod);
        $jsonData = $this->jsonSerializer->serialize($data);
        return $this->client->request($url, 'POST', $jsonData);
    }

    public function getMessages(): array
    {
        $url = $this->prepareUrl($this->getMsgMethod);
        $data = $this->client->request($url);

        return $data;
    }

    private function prepareUrl(string $telegramMethod): string
    {
        return $this->url . $telegramMethod;
    }

    private function prepareData(string $data, int $id): array
    {
        $formattedData['text']    = $data;
        $formattedData['chat_id'] = $id;
        return $formattedData;
    }

    private function checkConfigFields(ScopeConfigInterface $scopeConfig, $storeScope)
    {
        if (!$scopeConfig->isSetFlag(self::BASE_PATH . self::TOKEN, $storeScope)) {
            throw new Exception('There is no Telegram token!');
        }

        if (!$scopeConfig->isSetFlag(self::BASE_PATH . self::BASE_URL, $storeScope)) {
            throw new Exception('There is no Telegram url!');
        }

        if (!$scopeConfig->isSetFlag(self::BASE_PATH . self::SEND_MSG, $storeScope)) {
            throw new Exception('There is no Telegram send msg method!');
        }

        if (!$scopeConfig->isSetFlag(self::BASE_PATH . self::GET_MSG, $storeScope)) {
            throw new Exception('There is no Telegram get msg method!');
        }
    }
}
