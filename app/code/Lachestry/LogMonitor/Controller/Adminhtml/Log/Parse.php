<?php

declare(strict_types=1);

namespace Lachestry\LogMonitor\Controller\Adminhtml\Log;

use Lachestry\LogMonitor\Model\LogParser;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;

class Parse extends Action
{
    const ADMIN_RESOURCE = 'Lachestry_LogMonitor::logs';

    private LogParser $logParser;
    private JsonFactory $jsonFactory;

    public function __construct(
        Context $context,
        LogParser $logParser,
        JsonFactory $jsonFactory
    ) {
        parent::__construct($context);
        $this->logParser = $logParser;
        $this->jsonFactory = $jsonFactory;
    }

    public function execute()
    {
        $result = $this->jsonFactory->create();
        try {
            $count = $this->logParser->parse();
            return $result->setData([
                'success' => true,
                'count' => $count,
                'message' => __('Successfully parsed %1 log error(s)', $count)
            ]);
        } catch (\Exception $e) {
            return $result->setData([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}
