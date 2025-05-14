<?php
declare(strict_types=1);

namespace Lachestry\RabbitMQMonitor\Model;

use Lachestry\Configuration\Model\Config;
use Magento\Framework\Shell;
use Magento\Framework\Shell\CommandRendererInterface;
use Magento\Framework\Exception\LocalizedException;

class RabbitMQMonitor
{
    private Config $config;
    private Shell $shell;
    private CommandRendererInterface $commandRenderer;

    public function __construct(
        Config $config,
        Shell $shell,
        CommandRendererInterface $commandRenderer
    ) {
        $this->config = $config;
        $this->shell = $shell;
        $this->commandRenderer = $commandRenderer;
    }

    public function checkQueueStatus(string $queueName): array
    {
        try {
            $host = (string)$this->config->getConfigValue('lachestry/rabbitmq/host', 'store') ?: 'localhost';
            $port = (string)$this->config->getConfigValue('lachestry/rabbitmq/port', 'store') ?: '15672';
            $user = (string)$this->config->getConfigValue('lachestry/rabbitmq/user', 'store') ?: 'guest';
            $password = (string)$this->config->getConfigValue('lachestry/rabbitmq/password', 'store') ?: 'guest';
            $vhost = (string)$this->config->getConfigValue('lachestry/rabbitmq/vhost', 'store') ?: '/';

            $command = $this->commandRenderer->render(
                'curl',
                [
                    '-s',
                    '-u',
                    sprintf('%s:%s', $user, $password),
                    sprintf('http://%s:%s/api/queues/%s/%s', $host, $port, urlencode($vhost), urlencode($queueName))
                ]
            );

            $output = $this->shell->execute($command);
            $data = json_decode($output, true);

            if (!$data) {
                throw new LocalizedException(__('Failed to parse RabbitMQ response'));
            }

            if (isset($data['error'])) {
                throw new LocalizedException(__($data['error']));
            }

            return [
                'name' => $data['name'] ?? '',
                'messages' => $data['messages'] ?? 0,
                'consumers' => $data['consumers'] ?? 0,
                'memory' => $data['memory'] ?? 0,
                'state' => $data['state'] ?? 'unknown'
            ];
        } catch (\Exception $e) {
            return [
                'error' => $e->getMessage(),
                'state' => 'error'
            ];
        }
    }

    public function purgeQueue(string $queueName): void
    {
        try {
            $host = (string)$this->config->getConfigValue('lachestry/rabbitmq/host', 'store') ?: 'localhost';
            $port = (string)$this->config->getConfigValue('lachestry/rabbitmq/port', 'store') ?: '15672';
            $user = (string)$this->config->getConfigValue('lachestry/rabbitmq/user', 'store') ?: 'guest';
            $password = (string)$this->config->getConfigValue('lachestry/rabbitmq/password', 'store') ?: 'guest';
            $vhost = (string)$this->config->getConfigValue('lachestry/rabbitmq/vhost', 'store') ?: '/';

            $command = $this->commandRenderer->render(
                'curl',
                [
                    '-s',
                    '-X',
                    'DELETE',
                    '-u',
                    sprintf('%s:%s', $user, $password),
                    sprintf('http://%s:%s/api/queues/%s/%s/contents', $host, $port, urlencode($vhost), urlencode($queueName))
                ]
            );

            $this->shell->execute($command);
        } catch (\Exception $e) {
            throw new LocalizedException(__('Failed to purge queue: %1', $e->getMessage()));
        }
    }
} 