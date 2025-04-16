<?php

declare(strict_types=1);

namespace Lachestry\ProcessMonitor\Controller\Adminhtml\Process;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\View\Result\LayoutFactory;
use Magento\Framework\App\RequestInterface;
use Lachestry\ProcessMonitor\Model\Process;

class Index extends Action
{
    protected $resultPageFactory;
    protected $resultJsonFactory;
    protected $resultLayoutFactory;
    protected $process;
    protected $request;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        JsonFactory $resultJsonFactory,
        LayoutFactory $resultLayoutFactory,
        Process $process,
        RequestInterface $request
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resultLayoutFactory = $resultLayoutFactory;
        $this->process = $process;
        $this->request = $request;
    }

    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__('Daemon Processes'));
        return $resultPage;
    }

    public function refresh()
    {
        if (!$this->_isAllowed()) {
            return $this->resultJsonFactory->create()->setData([
                'success' => false,
                'message' => __('Access denied.')
            ]);
        }

        $resultJson = $this->resultJsonFactory->create();
        $processes = $this->process->getProcessList();
        
        return $resultJson->setData([
            'success' => true,
            'data' => $processes
        ]);
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Lachestry_ProcessMonitor::process_list_view');
    }
}
