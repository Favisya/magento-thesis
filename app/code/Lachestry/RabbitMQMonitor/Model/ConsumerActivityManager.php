<?php
declare(strict_types=1);

namespace Lachestry\RabbitMQMonitor\Model;

use Lachestry\RabbitMQMonitor\Model\ResourceModel\ConsumerActivity\CollectionFactory as ConsumerActivityCollectionFactory;
use Lachestry\RabbitMQMonitor\Model\ConsumerActivityFactory;
use Lachestry\RabbitMQMonitor\Model\ResourceModel\ConsumerActivity as ConsumerActivityResource;
use Magento\Framework\MessageQueue\Consumer\ConfigInterface as ConsumerConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\DeploymentConfig;

class ConsumerActivityManager
{
    protected ConsumerActivityCollectionFactory $collectionFactory;
    protected ConsumerActivityFactory $consumerActivityFactory;
    protected ConsumerActivityResource $consumerActivityResource;
    protected ConsumerConfigInterface $consumerConfig;
    protected DirectoryList $directoryList;
    protected DeploymentConfig $deploymentConfig;

    /**
     * @param ConsumerActivityCollectionFactory $collectionFactory
     * @param ConsumerActivityFactory $consumerActivityFactory
     * @param ConsumerActivityResource $consumerActivityResource
     * @param ConsumerConfigInterface $consumerConfig
     * @param DirectoryList $directoryList
     * @param DeploymentConfig $deploymentConfig
     */
    public function __construct(
        ConsumerActivityCollectionFactory $collectionFactory,
        ConsumerActivityFactory $consumerActivityFactory,
        ConsumerActivityResource $consumerActivityResource,
        ConsumerConfigInterface $consumerConfig,
        DirectoryList $directoryList,
        DeploymentConfig $deploymentConfig
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->consumerActivityFactory = $consumerActivityFactory;
        $this->consumerActivityResource = $consumerActivityResource;
        $this->consumerConfig = $consumerConfig;
        $this->directoryList = $directoryList;
        $this->deploymentConfig = $deploymentConfig;
    }

    /**
     * Обновляет статус консьюмеров
     * 
     * @return void
     */
    public function updateStatus(): void
    {
        // Получаем информацию о запущенных процессах консьюмеров
        $runningConsumers = $this->getRunningConsumersFromProcesses();
        
        // Получаем список всех консьюмеров из конфигурации
        $configuredConsumers = $this->getAllConfiguredConsumers();
        
        foreach ($configuredConsumers as $consumerName) {
            $pid = $runningConsumers[$consumerName] ?? null;
            $status = $pid ? 'Running' : 'Stopped';
            
            $this->updateConsumerStatus($consumerName, $status, $pid);
        }
    }
    
    /**
     * Обновляет статус конкретного консьюмера в БД
     * 
     * @param string $consumerName
     * @param string $status
     * @param int|null $pid
     * @return void
     */
    protected function updateConsumerStatus(string $consumerName, string $status, ?int $pid = null): void
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('consumer_name', $consumerName);
        
        $consumer = $collection->getFirstItem();
        
        if (!$consumer->getId()) {
            $consumer = $this->consumerActivityFactory->create();
            $consumer->setConsumerName($consumerName);
        }
        
        $consumer->setStatus($status);
        
        if ($pid) {
            $consumer->setPid($pid);
            // Обновляем время последней активности только если процесс запущен
            $consumer->setLastActivity(date('Y-m-d H:i:s'));
        }
        
        try {
            $this->consumerActivityResource->save($consumer);
        } catch (\Exception $e) {
            // Log error
        }
    }
    
    /**
     * Получает список запущенных консьюмеров из процессов
     * 
     * @return array
     */
    protected function getRunningConsumersFromProcesses(): array
    {
        $runningConsumers = [];
        
        // Проверяем PID файлы
        $this->checkPidFiles($runningConsumers);
        
        // Проверяем запущенные процессы
        $this->checkRunningProcesses($runningConsumers);
        
        return $runningConsumers;
    }
    
    /**
     * Проверяет PID файлы
     * 
     * @param array $runningConsumers
     * @return void
     */
    protected function checkPidFiles(array &$runningConsumers): void
    {
        $varPath = $this->directoryList->getPath(DirectoryList::VAR_DIR);
        $queuePath = $varPath . '/queue';
        
        if (file_exists($queuePath) && is_dir($queuePath)) {
            $files = scandir($queuePath);
            
            foreach ($files as $file) {
                if ($file === '.' || $file === '..') {
                    continue;
                }
                
                if (substr($file, -4) === '.pid') {
                    $consumerName = substr($file, 0, -4);
                    $consumerName = str_replace('_', ':', $consumerName);
                    
                    $pidFile = $queuePath . '/' . $file;
                    $pid = file_get_contents($pidFile);
                    
                    if ($pid && $this->isProcessRunning((int)$pid)) {
                        $runningConsumers[$consumerName] = (int)$pid;
                    }
                }
            }
        }
    }
    
    /**
     * Проверяет запущенные процессы
     * 
     * @param array $runningConsumers
     * @return void
     */
    protected function checkRunningProcesses(array &$runningConsumers): void
    {
        exec("ps aux | grep 'queue:consumers:start' | grep -v grep", $output);
        
        foreach ($output as $line) {
            if (preg_match('/queue:consumers:start\s+([^\s]+)/', $line, $matches)) {
                $consumerName = $matches[1];
                
                // Получаем PID процесса
                if (preg_match('/^\S+\s+(\d+)/', $line, $pidMatches)) {
                    $pid = (int)$pidMatches[1];
                    $runningConsumers[$consumerName] = $pid;
                }
            }
        }
    }
    
    /**
     * Проверяет, запущен ли процесс
     * 
     * @param int $pid
     * @return bool
     */
    protected function isProcessRunning(int $pid): bool
    {
        if (PHP_OS !== 'WINNT') {
            exec("ps -p $pid", $output, $returnCode);
            return $returnCode === 0;
        }
        
        exec("tasklist /FI \"PID eq $pid\"", $output);
        return count($output) > 1;
    }
    
    /**
     * Получает список всех сконфигурированных консьюмеров
     * 
     * @return array
     */
    protected function getAllConfiguredConsumers(): array
    {
        $consumers = [];
        
        // Получаем консьюмеры из конфигурации
        foreach ($this->consumerConfig->getConsumers() as $consumer) {
            $consumers[] = $consumer->getName();
        }
        
        // Получаем консьюмеры из cron_consumers_runner конфигурации
        $cronConsumers = $this->deploymentConfig->get('cron_consumers_runner/consumers');
        if (is_array($cronConsumers) && !empty($cronConsumers)) {
            foreach ($cronConsumers as $consumerName) {
                if (!in_array($consumerName, $consumers)) {
                    $consumers[] = $consumerName;
                }
            }
        }
        
        return $consumers;
    }
    
    /**
     * Получает запущенные консьюмеры
     * 
     * @return array
     */
    public function getRunningConsumers(): array
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('status', 'Running');
        $collection->addOrder('consumer_name', 'ASC');
        
        $result = [];
        foreach ($collection as $item) {
            $result[$item->getConsumerName()] = $item;
        }
        
        return $result;
    }
    
    /**
     * Получает данные активности консьюмеров из БД
     * 
     * @return array
     */
    public function getConsumerActivities(): array
    {
        $collection = $this->collectionFactory->create();
        $collection->addOrder('consumer_name', 'ASC');
        
        $result = [];
        foreach ($collection as $item) {
            $result[$item->getConsumerName()] = [
                'status' => $item->getStatus(),
                'pid' => $item->getPid(),
                'last_activity' => $item->getLastActivity()
            ];
        }
        
        return $result;
    }
} 