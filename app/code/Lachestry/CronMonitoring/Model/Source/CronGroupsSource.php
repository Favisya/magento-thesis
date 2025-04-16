<?php

declare(strict_types=1);

namespace Lachestry\CronMonitoring\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Cron\Model\Groups\Config\Data as CronGroups;

class CronGroupsSource implements OptionSourceInterface
{
    protected CronGroups $cronGroups;

    public function __construct(CronGroups $cronGroups)
    {
        $this->cronGroups = $cronGroups;
    }

    public function toOptionArray()
    {
        $result = [];
        foreach ($this->toArray() as $label) {
            $value = $label;
            $label = $this->processGroupName($label);

            $result[] = ['value' => $value, 'label' => $label];
        }
        return $result;
    }

    public function toArray(): array
    {
        return array_keys($this->cronGroups->get());
    }

    protected function processGroupName(string $name): string
    {
        $name = str_replace('_', ' ', $name);
        return mb_convert_case($name, MB_CASE_TITLE, "UTF-8");
    }
}
