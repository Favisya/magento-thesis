<?php

declare(strict_types=1);

namespace Lachestry\LogMonitor\Block\Adminhtml;

use Lachestry\LogMonitor\Model\ChartDataProvider;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Serialize\Serializer\Json;

class Chart extends Template
{
    protected ChartDataProvider $chartDataProvider;
    protected Json $jsonSerializer;

    public function __construct(
        Context $context,
        ChartDataProvider $chartDataProvider,
        Json $jsonSerializer,
        array $data = []
    ) {
        $this->chartDataProvider = $chartDataProvider;
        $this->jsonSerializer = $jsonSerializer;
        parent::__construct($context, $data);
    }

    public function getChartData(): string
    {
        $fromDate = $this->getRequest()->getParam('from');
        $toDate = $this->getRequest()->getParam('to');
        
        $data = $this->chartDataProvider->getChartData($fromDate, $toDate);
        
        return $this->jsonSerializer->serialize($data);
    }
}
