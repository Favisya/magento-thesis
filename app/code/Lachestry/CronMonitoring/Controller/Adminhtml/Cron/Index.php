<?php

declare(strict_types=1);

namespace Lachestry\CronMonitoring\Controller\Adminhtml\Cron;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Action\Action;

class Index extends Action
{
    protected PageFactory $pageFactory;

    public function __construct(
        Context $context,
        PageFactory $pageFactory
    ) {
        parent::__construct($context);
        $this->pageFactory = $pageFactory;
    }

    public function execute()
    {
        $resultPage = $this->pageFactory->create();

        $resultPage->setActiveMenu('Lachestry_CronMonitoring::cron_monitoring');
        $resultPage->addBreadcrumb(__('System'), __('System'));
        $resultPage->getConfig()->getTitle()->prepend('Cron Processes Monitor');

        return $resultPage;
    }
}
