<?php
declare(strict_types=1);

namespace Lachestry\RabbitMQMonitor\Controller\Adminhtml\Consumer;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Shell\CommandRenderer;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Shell;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Console\Cli;

class Start extends Action
{
    protected JsonFactory $resultJsonFactory;
    protected ScopeConfigInterface $scopeConfig;
    protected CommandRenderer $commandRenderer;
    protected DeploymentConfig $deploymentConfig;
    protected Shell $shell;
    protected File $file;
    
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        ScopeConfigInterface $scopeConfig,
        CommandRenderer $commandRenderer,
        DeploymentConfig $deploymentConfig,
        Shell $shell,
        File $file
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->scopeConfig = $scopeConfig;
        $this->commandRenderer = $commandRenderer;
        $this->deploymentConfig = $deploymentConfig;
        $this->shell = $shell;
        $this->file = $file;
        parent::__construct($context);
    }

    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        
        try {
            $consumerId = $this->getRequest()->getParam('consumer_id');
            
            if (!$consumerId) {
                throw new LocalizedException(__('Consumer ID is required'));
            }
            
            $maxMessages = (int)$this->scopeConfig->getValue('rabbitmq_monitor/general/max_messages') ?: 10000;
            $waitTimeoutSec = 1;
            
            $queueConfig = $this->deploymentConfig->get('queue');
            $consumersWaitForMessages = null;
            
            if (is_array($queueConfig) && isset($queueConfig['consumers_wait_for_messages'])) {
                if (is_array($queueConfig['consumers_wait_for_messages']) && 
                    isset($queueConfig['consumers_wait_for_messages'][$consumerId])) {
                    $consumersWaitForMessages = $queueConfig['consumers_wait_for_messages'][$consumerId];
                } else if (!is_array($queueConfig['consumers_wait_for_messages'])) {
                    $consumersWaitForMessages = $queueConfig['consumers_wait_for_messages'];
                }
            }
            
            if ($consumersWaitForMessages !== null) {
                $waitTimeoutSec = $consumersWaitForMessages ? 1 : 0;
            }
            
            $rootDir = $this->getRootDir();
            
            if (!$rootDir) {
                throw new LocalizedException(__('Could not determine Magento root directory'));
            }
            
            // Используем стандартный PHP
            $phpPath = 'php';
            
            $command = $phpPath . ' ' . $rootDir . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'magento ' 
                . 'queue:consumers:start ' . escapeshellarg($consumerId) . ' '
                . '--max-messages=' . $maxMessages . ' '
                . '--pid-file-path=' . escapeshellarg($rootDir . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'queue')
                . ($waitTimeoutSec !== null ? ' --wait-for-messages=' . $waitTimeoutSec : '');
                
            if (PHP_OS !== 'WINNT') {
                $command .= ' > /dev/null 2>&1 & echo $!';
                $output = $this->shell->execute($command);
                $pid = (int)trim($output);
                
                if (!$pid) {
                    throw new LocalizedException(__('Could not start consumer process'));
                }
            } else {
                // На Windows используем асинхронное выполнение через start
                $command = 'start /B "Magento Consumer ' . $consumerId . '" ' . $command;
                $this->shell->execute($command);
                $pid = 0; // На Windows не возвращаем PID
            }
            
            return $resultJson->setData([
                'success' => true,
                'message' => __('Consumer "%1" started successfully', $consumerId),
                'pid' => $pid
            ]);
        } catch (\Exception $e) {
            return $resultJson->setData([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    
    protected function getRootDir(): ?string
    {
        try {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $directoryList = $objectManager->get(\Magento\Framework\App\Filesystem\DirectoryList::class);
            return $directoryList->getRoot();
        } catch (\Exception $e) {
            return null;
        }
    }
    
    protected function _isAllowed(): bool
    {
        return $this->_authorization->isAllowed('Lachestry_RabbitMQMonitor::monitor');
    }
} 