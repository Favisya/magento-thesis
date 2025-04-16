<?php

declare(strict_types=1);

namespace Lachestry\RabbitMQMonitor\Model\ResourceModel\ConsumerActivity;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Lachestry\RabbitMQMonitor\Model\ConsumerActivity as ConsumerActivityModel;
use Lachestry\RabbitMQMonitor\Model\ResourceModel\ConsumerActivity as ConsumerActivityResource;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            ConsumerActivityModel::class,
            ConsumerActivityResource::class
        );
    }
}
