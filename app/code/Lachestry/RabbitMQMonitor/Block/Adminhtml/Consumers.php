<?php

declare(strict_types=1);

namespace Lachestry\RabbitMQMonitor\Block\Adminhtml;

use Magento\Backend\Block\Template;
use Magento\Framework\MessageQueue\Consumer\ConfigInterface as ConsumerConfigInterface;
use Magento\Framework\App\DeploymentConfig;
use Magento\MessageQueue\Model\Cron\ConsumersRunner;
use Magento\Framework\App\Filesystem\DirectoryList;
use Lachestry\RabbitMQMonitor\Model\ConsumerActivityManager;
use Lachestry\RabbitMQMonitor\Model\TopicList;

class Consumers extends Template
{
    protected $_template = 'Lachestry_RabbitMQMonitor::consumers.phtml';
    
    protected ConsumerConfigInterface $consumerConfig;
    protected DeploymentConfig $deploymentConfig;
    protected ConsumersRunner $consumersRunner;
    protected DirectoryList $directoryList;
    protected ConsumerActivityManager $consumerActivityManager;
    protected TopicList $topicList;

    public function __construct(
        Template\Context $context,
        ConsumerConfigInterface $consumerConfig,
        DeploymentConfig $deploymentConfig,
        ConsumersRunner $consumersRunner,
        DirectoryList $directoryList,
        ConsumerActivityManager $consumerActivityManager,
        TopicList $topicList,
        array $data = []
    ) {
        $this->consumerConfig = $consumerConfig;
        $this->deploymentConfig = $deploymentConfig;
        $this->consumersRunner = $consumersRunner;
        $this->directoryList = $directoryList;
        $this->consumerActivityManager = $consumerActivityManager;
        $this->topicList = $topicList;
        parent::__construct($context, $data);
    }

    public function getConsumers(): array
    {
        $result = [];
        $allowedConsumers = $this->getAllowedConsumers();
        $consumerConfig = $this->consumerConfig->getConsumers();
        $runningConsumers = $this->consumerActivityManager->getRunningConsumers();
        $consumerTopicMap = $this->topicList->getConsumerTopicMap();
        
        foreach ($consumerConfig as $consumer) {
            $consumerName = $consumer->getName();
            $isAllowed = empty($allowedConsumers) || in_array($consumerName, $allowedConsumers);
            $isRunning = array_key_exists($consumerName, $runningConsumers);
            
            $consumerData = [
                'name' => $consumerName,
                'connection' => $consumer->getConnection(),
                'queue' => $consumer->getQueue(),
                'topic' => $consumerTopicMap[$consumerName] ?? '-',
                'status' => $this->determineConsumerStatus($consumerName, $isRunning, $isAllowed)
            ];
            
            if ($isRunning) {
                $activityData = $runningConsumers[$consumerName];
                $consumerData['pid'] = $activityData->getPid();
                $lastActivity = $activityData->getLastActivity();
                
                if ($lastActivity) {
                    $consumerData['last_activity'] = $this->formatCustomDate($lastActivity);
                }
            }
            
            $result[] = $consumerData;
        }
        
        usort($result, function ($a, $b) {
            return strcmp($a['name'], $b['name']);
        });
        
        return $result;
    }
    
    protected function determineConsumerStatus(
        string $consumerName,
        bool $isRunning,
        bool $isAllowed
    ): string {
        if ($isRunning) {
            return 'Running';
        }
        
        if (!$isAllowed) {
            return 'Disabled';
        }
        
        return 'Stopped';
    }
    
    protected function getAllowedConsumers(): array
    {
        $queueConfig = $this->deploymentConfig->get('queue');
        
        if (isset($queueConfig['consumers_wait_for_messages'])) {
            $consumersConfig = $this->deploymentConfig->get('cron_consumers_runner/consumers');
            
            if (is_array($consumersConfig) && !empty($consumersConfig)) {
                return $consumersConfig;
            }
            
            if ($this->deploymentConfig->get('cron_consumers_runner/cron_run')) {
                $allConsumers = [];
                foreach ($this->consumerConfig->getConsumers() as $consumer) {
                    $allConsumers[] = $consumer->getName();
                }
                return $allConsumers;
            }
        }
        
        return [];
    }
    
    protected function formatCustomDate($dateTime): string
    {
        if (!$dateTime) {
            return '-';
        }
        
        $date = new \DateTime($dateTime);
        return $date->format('Y-m-d H:i:s');
    }
}
