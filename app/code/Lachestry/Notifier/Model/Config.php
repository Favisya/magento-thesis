<?php
declare(strict_types=1);

namespace Lachestry\Notifier\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Config
{
    private const PATH_ENABLED = 'lachestry_notifier/general/enabled';
    private const PATH_NOTIFY_INDEXER = 'lachestry_notifier/events/notify_indexer';
    private const PATH_NOTIFY_CRON = 'lachestry_notifier/events/notify_cron';
    private const PATH_NOTIFY_QUEUE = 'lachestry_notifier/events/notify_queue';
    private const PATH_NOTIFY_API = 'lachestry_notifier/events/notify_api';

    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig
    ) {}

    public function isEnabled(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::PATH_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function isIndexerNotificationEnabled(?int $storeId = null): bool
    {
        return $this->isEnabled($storeId) && $this->scopeConfig->isSetFlag(
            self::PATH_NOTIFY_INDEXER,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function isCronNotificationEnabled(?int $storeId = null): bool
    {
        return $this->isEnabled($storeId) && $this->scopeConfig->isSetFlag(
            self::PATH_NOTIFY_CRON,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function isQueueNotificationEnabled(?int $storeId = null): bool
    {
        return $this->isEnabled($storeId) && $this->scopeConfig->isSetFlag(
            self::PATH_NOTIFY_QUEUE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function isApiNotificationEnabled(?int $storeId = null): bool
    {
        return $this->isEnabled($storeId) && $this->scopeConfig->isSetFlag(
            self::PATH_NOTIFY_API,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
