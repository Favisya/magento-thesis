<?php

declare(strict_types=1);

namespace Lachestry\CronMonitoring\Block\Adminhtml\Renderer;

use Lachestry\CronMonitoring\Model\Source\CronGroupsSource;
use Magento\Framework\View\Element\Html\Select;
use Magento\Framework\View\Element\Context;

class GroupNamesSelect extends Select
{
    protected CronGroupsSource $cronGroupsSource;

    public function __construct(
        Context $context,
        CronGroupsSource $cronGroupsSource,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->cronGroupsSource = $cronGroupsSource;
    }

    public function setInputName($value)
    {
        return $this->setName($value . '[]');
    }

    public function _toHtml(): string
    {
        if (!$this->getOptions()) {
            $this->setOptions($this->cronGroupsSource->toOptionArray());
        }
        return parent::_toHtml();
    }
}
