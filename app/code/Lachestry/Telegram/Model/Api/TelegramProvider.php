<?php
declare(strict_types=1);

namespace Lachestry\Telegram\Model\Api;

use Lachestry\Telegram\Api\NotificationInterface;
use Lachestry\Telegram\Model\Api\Http\Client;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Lachestry\Telegram\Model\Config;
use Magento\Setup\Exception;
use Psr\Log\LoggerInterface;

class TelegramProvider implements NotificationInterface
{
    protected string $url;
    protected mixed $token;
    protected mixed $baseUrl;
    protected mixed $getMsgMethod;
    protected mixed $sendMsgMethod;

    public function __construct(
        protected readonly Client $client,
        protected readonly Json $jsonSerializer,
        protected readonly LoggerInterface $logger,
        protected readonly Config $config
    ) {
        $this->token         = $this->config->getToken();
        $this->sendMsgMethod = $this->config->getSendMsgMethod();
        $this->getMsgMethod  = $this->config->getGetMsgMethod();
        $this->baseUrl       = $this->config->getBaseUrl();

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

        return $this->client->request($url) ?? [];
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
        if (!$this->config->getToken()) {
            throw new Exception('There is no Telegram token!');
        }

        if (!$this->config->getBaseUrl()) {
            throw new Exception('There is no Telegram url!');
        }

        if (!$this->config->getSendMsgMethod()) {
            throw new Exception('There is no Telegram send msg method!');
        }

        if (!$this->config->getGetMsgMethod()) {
            throw new Exception('There is no Telegram get msg method!');
        }
    }
}
