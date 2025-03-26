<?php
declare(strict_types=1);

namespace Lachestry\LogMonitor\Model\ResourceModel\LogError;

use Lachestry\LogMonitor\Model\LogError;
use Lachestry\LogMonitor\Model\ResourceModel\LogError as LogErrorResource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'entity_id';

    protected function _construct()
    {
        $this->_init(LogError::class, LogErrorResource::class);
    }
} 