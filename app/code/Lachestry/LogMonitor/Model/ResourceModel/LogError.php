<?php
declare(strict_types=1);

namespace Lachestry\LogMonitor\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class LogError extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('lachestry_log_errors', 'entity_id');
    }
} 