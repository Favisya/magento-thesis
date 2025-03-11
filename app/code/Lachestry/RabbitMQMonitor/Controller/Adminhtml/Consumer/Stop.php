<?php
declare(strict_types=1);

namespace Lachestry\RabbitMQMonitor\Controller\Adminhtml\Consumer;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Shell;
use Magento\Framework\Exception\LocalizedException;

class Stop extends Action
{
    protected JsonFactory $resultJsonFactory;
    protected Shell $shell;
    
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        Shell $shell
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->shell = $shell;
        parent::__construct($context);
    }

    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        
        try {
            $consumerId = $this->getRequest()->getParam('consumer_id');
            $pid = (int)$this->getRequest()->getParam('pid');
            
            if (!$consumerId) {
                throw new LocalizedException(__('Consumer ID is required'));
            }
            
            if (!$pid) {
                throw new LocalizedException(__('Process ID is required'));
            }
            
            $result = $this->stopProcess($pid);
            
            if ($result) {
                return $resultJson->setData([
                    'success' => true,
                    'message' => __('Consumer "%1" (PID: %2) stopped successfully', $consumerId, $pid)
                ]);
            } else {
                return $resultJson->setData([
                    'success' => false,
                    'message' => __('Consumer "%1" (PID: %2) could not be stopped or is already stopped', $consumerId, $pid)
                ]);
            }
        } catch (\Exception $e) {
            return $resultJson->setData([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    
    protected function stopProcess(int $pid): bool
    {
        try {
            if (PHP_OS === 'WINNT') {
                $command = "taskkill /F /PID $pid";
            } else {
                $command = "kill -9 $pid";
            }
            
            $this->shell->execute($command);
            
            // Проверяем, остановлен ли процесс
            return !$this->isProcessRunning($pid);
        } catch (\Exception $e) {
            return false;
        }
    }
    
    protected function isProcessRunning(int $pid): bool
    {
        try {
            $command = PHP_OS === 'WINNT'
                ? "tasklist /FI \"PID eq $pid\" | find \"$pid\""
                : "ps -p $pid | grep $pid";
                
            $output = $this->shell->execute($command);
            return !empty($output);
        } catch (\Exception $e) {
            return false;
        }
    }
    
    protected function _isAllowed(): bool
    {
        return $this->_authorization->isAllowed('Lachestry_RabbitMQMonitor::monitor');
    }
} 