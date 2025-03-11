<?php
declare(strict_types=1);

namespace Lachestry\RabbitMQMonitor\Cron;

use Lachestry\RabbitMQMonitor\Model\ConsumerActivityManager;
use Psr\Log\LoggerInterface;

class UpdateConsumerStatus
{
    /**
     * @var ConsumerActivityManager
     */
    private $consumerActivityManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param ConsumerActivityManager $consumerActivityManager
     * @param LoggerInterface $logger
     */
    public function __construct(
        ConsumerActivityManager $consumerActivityManager,
        LoggerInterface $logger
    ) {
        $this->consumerActivityManager = $consumerActivityManager;
        $this->logger = $logger;
    }

    /**
     * Выполняет обновление статусов консьюмеров
     * 
     * @return void
     */
    public function execute(): void
    {
        try {
            $this->consumerActivityManager->updateStatus();
        } catch (\Exception $e) {
            $this->logger->error('Ошибка при обновлении статуса консьюмеров: ' . $e->getMessage());
        }
    }
} 