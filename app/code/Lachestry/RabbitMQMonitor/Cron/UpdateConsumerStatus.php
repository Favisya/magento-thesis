<?php

declare(strict_types=1);

namespace Lachestry\RabbitMQMonitor\Cron;

use Lachestry\RabbitMQMonitor\Model\ConsumerActivityManager;
use Psr\Log\LoggerInterface;

class UpdateConsumerStatus
{
    protected ConsumerActivityManager $consumerActivityManager;
    protected LoggerInterface $logger;

    public function __construct(
        ConsumerActivityManager $consumerActivityManager,
        LoggerInterface $logger
    ) {
        $this->consumerActivityManager = $consumerActivityManager;
        $this->logger = $logger;
    }

    public function execute(): void
    {
        try {
            $this->consumerActivityManager->updateStatus();
        } catch (\Exception $e) {
            $this->logger->error('Ошибка при обновлении статуса консьюмеров: ' . $e->getMessage());
        }
    }
}
