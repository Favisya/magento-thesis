<?php
declare(strict_types=1);

namespace Lachestry\Notifier\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Config
{
    private const XML_PATH_ENABLED = 'lachestry_notifier/general/enabled';
    private const XML_PATH_NOTIFY_INDEXER = 'lachestry_notifier/events/notify_indexer';
    private const XML_PATH_NOTIFY_CRON = 'lachestry_notifier/events/notify_cron';
    private const XML_PATH_NOTIFY_QUEUE = 'lachestry_notifier/events/notify_queue';
    private const XML_PATH_NOTIFY_API = 'lachestry_notifier/events/notify_api';

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig
    ) {
    }

    /**
     * Check if module is enabled
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isEnabled(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check if indexer notifications are enabled
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isIndexerNotificationEnabled(?int $storeId = null): bool
    {
        return $this->isEnabled($storeId) && $this->scopeConfig->isSetFlag(
            self::XML_PATH_NOTIFY_INDEXER,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check if cron notifications are enabled
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isCronNotificationEnabled(?int $storeId = null): bool
    {
        return $this->isEnabled($storeId) && $this->scopeConfig->isSetFlag(
            self::XML_PATH_NOTIFY_CRON,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check if queue notifications are enabled
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isQueueNotificationEnabled(?int $storeId = null): bool
    {
        return $this->isEnabled($storeId) && $this->scopeConfig->isSetFlag(
            self::XML_PATH_NOTIFY_QUEUE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check if API notifications are enabled
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isApiNotificationEnabled(?int $storeId = null): bool
    {
        return $this->isEnabled($storeId) && $this->scopeConfig->isSetFlag(
            self::XML_PATH_NOTIFY_API,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
