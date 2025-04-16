<?php

declare(strict_types=1);

namespace Lachestry\RabbitMQMonitor\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class ConsumerActivity extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('rabbitmq_consumer_activity', 'entity_id');
    }
}
