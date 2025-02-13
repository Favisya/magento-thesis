<?php
declare(strict_types=1);

namespace Lachestry\Telegram\Model\Source;

use Magento\Framework\Option\ArrayInterface;

class IsActive implements ArrayInterface
{
    public function toOptionArray(): array
    {
        return [
            ['value' => 0, 'label' => __('No')],
            ['value' => 1, 'label' => __('Yes')]
        ];
    }
}
