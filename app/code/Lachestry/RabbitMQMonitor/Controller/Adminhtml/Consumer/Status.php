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

class Status extends Action
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
            
            $rootDir = $this->getRootDir();
            
            if (!$rootDir) {
                throw new LocalizedException(__('Could not determine Magento root directory'));
            }
            
            $pidFile = $rootDir . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'queue' . DIRECTORY_SEPARATOR . $consumerId . '.pid';
            
            if (!$this->file->isExists($pidFile)) {
                return $resultJson->setData([
                    'success' => true,
                    'status' => 'stopped',
                    'message' => __('Consumer "%1" is not running', $consumerId)
                ]);
            }
            
            $pid = (int)$this->file->fileGetContents($pidFile);
            
            if (!$pid) {
                return $resultJson->setData([
                    'success' => true,
                    'status' => 'stopped',
                    'message' => __('Consumer "%1" is not running', $consumerId)
                ]);
            }
            
            if (PHP_OS !== 'WINNT') {
                $command = 'ps -p ' . $pid . ' -o comm=';
                $output = $this->shell->execute($command);
                
                if (empty($output)) {
                    return $resultJson->setData([
                        'success' => true,
                        'status' => 'stopped',
                        'message' => __('Consumer "%1" is not running', $consumerId)
                    ]);
                }
            } else {
                $command = 'tasklist /FI "PID eq ' . $pid . '" /FO CSV /NH';
                $output = $this->shell->execute($command);
                
                if (empty($output)) {
                    return $resultJson->setData([
                        'success' => true,
                        'status' => 'stopped',
                        'message' => __('Consumer "%1" is not running', $consumerId)
                    ]);
                }
            }
            
            return $resultJson->setData([
                'success' => true,
                'status' => 'running',
                'pid' => $pid,
                'message' => __('Consumer "%1" is running with PID %2', $consumerId, $pid)
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