<?php

declare(strict_types=1);

namespace Lachestry\Notifier\Model\MessageQueue;

use Magento\Framework\MessageQueue\Consumer as MagentoConsumer;
use Magento\Framework\MessageQueue\ConsumerConfigurationInterface;

class Consumer extends MagentoConsumer
{
    /**
     * Возвращает объект конфигурации консьюмера
     *
     * @return ConsumerConfigurationInterface|null
     */
    public function getConsumerConfiguration(): ?ConsumerConfigurationInterface
    {
        return $this->configuration;
    }
} 