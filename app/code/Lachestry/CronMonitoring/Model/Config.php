<?php

declare(strict_types=1);

namespace Lachestry\CronMonitoring\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\Serializer\Json;

class Config
{
    protected const TIME_BEFORE_CRON_THRESHOLD = 'system/cron_monitoring/time_before_stuck';
    protected const ROWS_IN_CRON_CARD          = 'system/cron_monitoring/rows_in_cron_group_card';
    protected const ROWS_HEIGHT_IN_CRON_CARD   = 'system/cron_monitoring/row_height_in_cron_group_card';

    protected const GROUP = 'group_name';

    protected ScopeConfigInterface $scopeConfig;
    protected Json $jsonSerializer;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Json $jsonSerializer
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->jsonSerializer = $jsonSerializer;
    }

    public function getCronGroupTimeMap(): array
    {
        $jsonData = $this->scopeConfig->getValue(self::TIME_BEFORE_CRON_THRESHOLD);
        return $this->handleGroupMap($jsonData);
    }

    public function getRowsInCard(): int
    {
        return (int) $this->scopeConfig->getValue(self::ROWS_IN_CRON_CARD);
    }

    public function getRowHeight(): float
    {
        return (float) $this->scopeConfig->getValue(self::ROWS_HEIGHT_IN_CRON_CARD);
    }

    protected function handleGroupMap(?string $json): array
    {
        if (!$json) {
            return [];
        }

        $serialized = $this->jsonSerializer->unserialize($json);

        $result = [];
        foreach ($serialized as &$item) {
            $item['group_name'] = reset($item['group_name']);
            $result[$item['group_name']] = $item;
        }

        return $result;
    }
}
