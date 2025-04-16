<?php

declare(strict_types=1);

namespace Lachestry\RabbitMQMonitor\Model;

use Magento\Framework\Model\AbstractModel;

class ConsumerActivity extends AbstractModel
{
    protected function _construct()
    {
        $this->_init(\Lachestry\RabbitMQMonitor\Model\ResourceModel\ConsumerActivity::class);
    }
}
