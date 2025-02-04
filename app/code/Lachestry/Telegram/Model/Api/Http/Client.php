<?php
declare(strict_types=1);

namespace Lachestry\Telegram\Model\Api\Http;

use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\HTTP\Client\CurlFactory;
use Magento\Framework\Serialize\Serializer\Json;
use Psr\Log\LoggerInterface;
use Magento\Setup\Exception;

class Client
{
    public function __construct(
        protected readonly CurlFactory $clientFactory,
        protected readonly Json $jsonSerializer,
        protected readonly LoggerInterface $logger,
    ) {}

    public function request(string $url, string $method = 'GET', $rawData = null): array
    {
        $client = $this->buildClient();

        try {
            if ($method == 'POST' || !is_null($rawData)) {
                $client->setHeaders([
                   'Content-Type' => 'application/json',
                ]);
                $client->post($url, $rawData);
            }

            $this->logger->info(
                'telegram api request',
                [
                    'response' => $client->getBody(),
                    'status'   => $client->getStatus(),
                ]
            );

            $statusGroup = (int) ($client->getStatus() / 100);
            if ($statusGroup !== 2) {
                throw new \Exception('success status check failed');
            }

            $body = $client->getBody();
            return $this->jsonSerializer->unserialize($body);
        } catch (\Exception $e) {
            $this->logger->error(
                'Error to send request',
                ['exception' => $e->__toString()]
            );
            throw new Exception('Error with telegram request');
        }
    }

    protected function buildClient(): Curl
    {
        return $this->clientFactory->create();
    }
}
