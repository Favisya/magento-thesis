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

class ListConsumers extends Action
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
            $rootDir = $this->getRootDir();
            
            if (!$rootDir) {
                throw new LocalizedException(__('Could not determine Magento root directory'));
            }
            
            $phpPath = 'php';
            
            $command = $phpPath . ' ' . $rootDir . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'magento ' 
                . 'queue:consumers:list';
                
            $output = $this->shell->execute($command);
            
            $consumers = [];
            $lines = explode("\n", $output);
            
            foreach ($lines as $line) {
                $line = trim($line);
                if (!empty($line)) {
                    $consumers[] = [
                        'id' => $line,
                        'name' => $line
                    ];
                }
            }
            
            return $resultJson->setData([
                'success' => true,
                'consumers' => $consumers
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