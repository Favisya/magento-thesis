<?php
declare(strict_types=1);

namespace Lachestry\RabbitMQMonitor\Model;

use Magento\Framework\MessageQueue\Topology\ConfigInterface as TopologyConfigInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\MessageQueue\Consumer\ConfigInterface as ConsumerConfigInterface;

class TopicList
{
    protected TopologyConfigInterface $topologyConfig;
    protected ResourceConnection $resourceConnection;
    protected DeploymentConfig $deploymentConfig;
    protected ConsumerConfigInterface $consumerConfig;

    public function __construct(
        TopologyConfigInterface $topologyConfig,
        ResourceConnection $resourceConnection,
        DeploymentConfig $deploymentConfig,
        ConsumerConfigInterface $consumerConfig
    ) {
        $this->topologyConfig = $topologyConfig;
        $this->resourceConnection = $resourceConnection;
        $this->deploymentConfig = $deploymentConfig;
        $this->consumerConfig = $consumerConfig;
    }

    public function getTopics(): array
    {
        $topics = [];
        
        try {
            $topologyConfigData = $this->topologyConfig->getExchanges();
            foreach ($topologyConfigData as $exchange) {
                $exchangeName = $exchange->getName();
                if (!in_array($exchangeName, $topics)) {
                    $topics[] = $exchangeName;
                }
            }
        } catch (\Exception $e) {
        }
        
        try {
            $connection = $this->resourceConnection->getConnection();
            $tableNames = $connection->listTables();
            if (in_array($this->resourceConnection->getTableName('queue_topic'), $tableNames)) {
                $queueTopicTable = $this->resourceConnection->getTableName('queue_topic');
                $queueTopicData = $connection->fetchAll("SELECT topic_name FROM {$queueTopicTable}");
                
                foreach ($queueTopicData as $row) {
                    if (!in_array($row['topic_name'], $topics)) {
                        $topics[] = $row['topic_name'];
                    }
                }
            }
        } catch (\Exception $e) {
        }
        
        try {
            $queueConfig = $this->deploymentConfig->get('queue');
            if (is_array($queueConfig) && isset($queueConfig['topics']) && is_array($queueConfig['topics'])) {
                foreach (array_keys($queueConfig['topics']) as $topic) {
                    if (!in_array($topic, $topics)) {
                        $topics[] = $topic;
                    }
                }
            }
        } catch (\Exception $e) {
        }
        
        try {
            $consumers = $this->consumerConfig->getConsumers();
            
            foreach ($consumers as $consumer) {
                $queueName = $consumer->getQueue();
                if (!in_array($queueName, $topics)) {
                    $topics[] = $queueName;
                }
            }
        } catch (\Exception $e) {
        }
        
        try {
            $cronConsumers = $this->deploymentConfig->get('cron_consumers_runner/consumers');
            
            if (is_array($cronConsumers)) {
                foreach ($cronConsumers as $consumer) {
                    if (!in_array($consumer, $topics)) {
                        $topics[] = $consumer;
                    }
                }
            }
        } catch (\Exception $e) {
        }
        
        sort($topics);
        return $topics;
    }

    public function getConsumerTopicMap(): array
    {
        $result = [];
        
        try {
            $connection = $this->resourceConnection->getConnection();
            $tableNames = $connection->listTables();
            
            if (in_array($this->resourceConnection->getTableName('queue_consumer_link'), $tableNames) && 
                in_array($this->resourceConnection->getTableName('queue_topic'), $tableNames)) {
                
                $queueConsumerLinkTable = $this->resourceConnection->getTableName('queue_consumer_link');
                $queueTopicTable = $this->resourceConnection->getTableName('queue_topic');
                
                $queueConsumerData = $connection->fetchAll("
                    SELECT cl.consumer_name, t.topic_name 
                    FROM {$queueConsumerLinkTable} cl
                    JOIN {$queueTopicTable} t ON cl.topic_id = t.id
                ");
                
                foreach ($queueConsumerData as $row) {
                    $result[$row['consumer_name']] = $row['topic_name'];
                }
            }
            
            if (in_array($this->resourceConnection->getTableName('queue_message_status'), $tableNames)) {
                $queueMessageStatusTable = $this->resourceConnection->getTableName('queue_message_status');
                
                $queueMessageData = $connection->fetchAll("
                    SELECT DISTINCT queue_id, topic_name 
                    FROM {$queueMessageStatusTable}
                ");
                
                foreach ($queueMessageData as $row) {
                    $result[$row['queue_id']] = $row['topic_name'];
                }
            }
        } catch (\Exception $e) {
        }
        
        try {
            $queueConfig = $this->deploymentConfig->get('queue');
            
            if (is_array($queueConfig) && isset($queueConfig['topics']) && is_array($queueConfig['topics'])) {
                foreach ($queueConfig['topics'] as $topic => $config) {
                    if (isset($config['consumer']) && !isset($result[$config['consumer']])) {
                        $result[$config['consumer']] = $topic;
                    }
                }
            }
        } catch (\Exception $e) {
        }
        
        try {
            $consumers = $this->consumerConfig->getConsumers();
            
            foreach ($consumers as $consumer) {
                $consumerName = $consumer->getName();
                $queueName = $consumer->getQueue();
                
                if (!isset($result[$consumerName])) {
                    $result[$consumerName] = $queueName;
                }
            }
        } catch (\Exception $e) {
        }
        
        ksort($result);
        return $result;
    }

    public function getTopicForConsumer(string $consumerName): ?string
    {
        $map = $this->getConsumerTopicMap();
        return $map[$consumerName] ?? null;
    }
}