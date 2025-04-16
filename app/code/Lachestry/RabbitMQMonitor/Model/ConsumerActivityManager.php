<?php

declare(strict_types=1);

namespace Lachestry\RabbitMQMonitor\Model;

use Lachestry\RabbitMQMonitor\Model\ResourceModel\ConsumerActivity\CollectionFactory as ConsumerActivityCollectionFactory;
use Lachestry\RabbitMQMonitor\Model\ConsumerActivityFactory;
use Lachestry\RabbitMQMonitor\Model\ResourceModel\ConsumerActivity as ConsumerActivityResource;
use Magento\Framework\MessageQueue\Consumer\ConfigInterface as ConsumerConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Shell;
use Magento\Framework\Exception\LocalizedException;

class ConsumerActivityManager
{
    protected ConsumerActivityCollectionFactory $collectionFactory;
    protected ConsumerActivityFactory $consumerActivityFactory;
    protected ConsumerActivityResource $consumerActivityResource;
    protected ConsumerConfigInterface $consumerConfig;
    protected DirectoryList $directoryList;
    protected DeploymentConfig $deploymentConfig;
    protected Shell $shell;

    /**
     * @param ConsumerActivityCollectionFactory $collectionFactory
     * @param ConsumerActivityFactory $consumerActivityFactory
     * @param ConsumerActivityResource $consumerActivityResource
     * @param ConsumerConfigInterface $consumerConfig
     * @param DirectoryList $directoryList
     * @param DeploymentConfig $deploymentConfig
     * @param Shell $shell
     */
    public function __construct(
        ConsumerActivityCollectionFactory $collectionFactory,
        ConsumerActivityFactory $consumerActivityFactory,
        ConsumerActivityResource $consumerActivityResource,
        ConsumerConfigInterface $consumerConfig,
        DirectoryList $directoryList,
        DeploymentConfig $deploymentConfig,
        Shell $shell
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->consumerActivityFactory = $consumerActivityFactory;
        $this->consumerActivityResource = $consumerActivityResource;
        $this->consumerConfig = $consumerConfig;
        $this->directoryList = $directoryList;
        $this->deploymentConfig = $deploymentConfig;
        $this->shell = $shell;
    }

    /**
     * Обновляет статус консьюмеров
     *
     * @return void
     */
    public function updateStatus(): void
    {
        $runningConsumers = $this->getRunningConsumersFromProcesses();
        
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
            $consumer->setLastActivity(date('Y-m-d H:i:s'));
        }
        
        try {
            $this->consumerActivityResource->save($consumer);
        } catch (\Exception $e) {
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
        
        $this->checkPidFiles($runningConsumers);
        
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
        try {
            $command = PHP_OS === 'WINNT'
                ? 'tasklist | findstr "queue:consumers:start"'
                : "ps aux | grep 'queue:consumers:start' | grep -v grep";
            
            $output = $this->shell->execute($command);
            
            foreach (explode("\n", $output) as $line) {
                if (empty($line)) {
                    continue;
                }
                
                if (preg_match('/queue:consumers:start\s+([^\s]+)/', $line, $matches)) {
                    $consumerName = $matches[1];
                    
                    if (preg_match('/^\S+\s+(\d+)/', $line, $pidMatches)) {
                        $pid = (int)$pidMatches[1];
                        $runningConsumers[$consumerName] = $pid;
                    }
                }
            }
        } catch (LocalizedException $e) {
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
        try {
            $command = PHP_OS === 'WINNT'
                ? "tasklist /FI \"PID eq $pid\" | find \"$pid\""
                : "ps -p $pid | grep $pid";
                
            $output = $this->shell->execute($command);
            return !empty($output);
        } catch (LocalizedException $e) {
            return false;
        }
    }
    
    /**
     * Получает список всех сконфигурированных консьюмеров
     *
     * @return array
     */
    protected function getAllConfiguredConsumers(): array
    {
        $consumers = [];
        
        foreach ($this->consumerConfig->getConsumers() as $consumer) {
            $consumers[] = $consumer->getName();
        }
        
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
