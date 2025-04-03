<?php

declare(strict_types=1);

namespace Lachestry\LogMonitor\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

class SeverityOptions implements OptionSourceInterface
{
    public function toOptionArray(): array
    {
        return [
            ['value' => 'debug', 'label' => __('Debug')],
            ['value' => 'info', 'label' => __('Info')],
            ['value' => 'notice', 'label' => __('Notice')],
            ['value' => 'warning', 'label' => __('Warning')],
            ['value' => 'error', 'label' => __('Error')],
            ['value' => 'critical', 'label' => __('Critical')],
            ['value' => 'alert', 'label' => __('Alert')],
            ['value' => 'emergency', 'label' => __('Emergency')]
        ];
    }
}
