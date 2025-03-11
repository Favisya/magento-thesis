<?php
declare(strict_types=1);

namespace Lachestry\RabbitMQMonitor\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\MessageQueue\PublisherInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\MessageQueue\Consumer\ConfigInterface as ConsumerConfigInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\MessageQueue\Topology\ConfigInterface as TopologyConfigInterface;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\MessageQueue\Consumer\Config\ConsumerConfigItemInterface;
use Lachestry\RabbitMQMonitor\Model\TopicList;

class TestQueueMessage extends Command
{
    const ARGUMENT_TOPIC = 'topic';
    const OPTION_MESSAGE = 'message';
    const OPTION_CONSUMER = 'consumer';
    const OPTION_LIST = 'list';

    protected PublisherInterface $publisher;
    protected SerializerInterface $serializer;
    protected ConsumerConfigInterface $consumerConfig;
    protected ResourceConnection $resourceConnection;
    protected TopologyConfigInterface $topologyConfig;
    protected DeploymentConfig $deploymentConfig;
    protected TopicList $topicList;

    public function __construct(
        PublisherInterface $publisher,
        SerializerInterface $serializer,
        ConsumerConfigInterface $consumerConfig,
        ResourceConnection $resourceConnection,
        TopologyConfigInterface $topologyConfig,
        DeploymentConfig $deploymentConfig,
        TopicList $topicList,
        string $name = null
    ) {
        $this->publisher = $publisher;
        $this->serializer = $serializer;
        $this->consumerConfig = $consumerConfig;
        $this->resourceConnection = $resourceConnection;
        $this->topologyConfig = $topologyConfig;
        $this->deploymentConfig = $deploymentConfig;
        $this->topicList = $topicList;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setName('rabbitmq:test:message')
            ->setDescription('Send a test message to RabbitMQ')
            ->addArgument(
                self::ARGUMENT_TOPIC,
                InputArgument::OPTIONAL,
                'Topic name'
            )
            ->addOption(
                self::OPTION_MESSAGE,
                'm',
                InputOption::VALUE_OPTIONAL,
                'Message content',
                'This is a test message'
            )
            ->addOption(
                self::OPTION_CONSUMER,
                'c',
                InputOption::VALUE_OPTIONAL,
                'Consumer name to find associated topic'
            )
            ->addOption(
                self::OPTION_LIST,
                'l',
                InputOption::VALUE_NONE,
                'List all available topics and consumers'
            );
        
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption(self::OPTION_LIST)) {
            $this->listTopicsAndConsumers($output);
            return 0;
        }

        $consumerName = $input->getOption(self::OPTION_CONSUMER);
        $topicName = $input->getArgument(self::ARGUMENT_TOPIC);
        
        if ($consumerName && !$topicName) {
            $topicName = $this->getTopicForConsumer($consumerName);
            
            if (!$topicName) {
                $output->writeln('<e>Не удалось найти топик для консьюмера: ' . $consumerName . '</e>');
                return 1;
            }
            
            $output->writeln('<info>Найден топик для консьюмера ' . $consumerName . ': ' . $topicName . '</info>');
        }
        
        if (!$topicName) {
            $output->writeln('<error>Не указан топик. Используйте аргумент topic или опцию --consumer</error>');
            return 1;
        }
        
        $message = $input->getOption(self::OPTION_MESSAGE);
        
        try {
            $this->publisher->publish($topicName, $message);
            $output->writeln('<info>Сообщение успешно отправлено в топик: ' . $topicName . '</info>');
            $output->writeln('<info>Содержимое: ' . $message . '</info>');
            return 0;
        } catch (\Exception $e) {
            $output->writeln('<error>Ошибка при отправке сообщения: ' . $e->getMessage() . '</error>');
            return 1;
        }
    }

    protected function listTopicsAndConsumers(OutputInterface $output)
    {
        $topics = $this->getAllTopics();
        $consumers = $this->getConsumerTopicMap();
        
        $output->writeln('<info>Доступные топики:</info>');
        foreach ($topics as $topic) {
            $output->writeln('- ' . $topic);
        }
        
        $output->writeln('');
        $output->writeln('<info>Доступные консьюмеры и их топики:</info>');
        foreach ($consumers as $consumer => $topic) {
            $output->writeln('- ' . $consumer . ' => ' . ($topic ?: 'топик не найден'));
        }
    }

    protected function getTopicForConsumer(string $consumerName): ?string
    {
        $consumer = $this->findConsumerByName($consumerName);
        
        if ($consumer) {
            $topic = $this->findTopicByHandler($consumer);
            if ($topic) {
                return $topic;
            }
        }
        
        $consumerTopicMap = $this->getConsumerTopicMap();
        return $consumerTopicMap[$consumerName] ?? null;
    }

    protected function findConsumerByName(string $consumerName): ?ConsumerConfigItemInterface
    {
        foreach ($this->consumerConfig->getConsumers() as $consumer) {
            if ($consumer->getName() === $consumerName) {
                return $consumer;
            }
        }
        return null;
    }

    protected function findTopicByHandler(ConsumerConfigItemInterface $consumer): string
    {
        $queueName = $consumer->getQueue();
        $consumerName = $consumer->getName();
        
        $consumerTopicMap = [];
        
        $topics = $this->getAllTopics();
        
        if (in_array($queueName, $topics)) {
            return $queueName;
        }
        
        if (in_array($consumerName, $topics)) {
            return $consumerName;
        }
        
        foreach ($topics as $topic) {
            if (strpos($topic, $queueName) !== false || strpos($topic, $consumerName) !== false) {
                return $topic;
            }
        }
        
        try {
            $handlers = $consumer->getHandlers();
            foreach ($handlers as $handler) {
                $method = $handler->getMethod();
                $type = $handler->getType();
                
                if ($type && $method) {
                    return $type;
                }
            }
        } catch (\Exception $e) {
            return '';
        }
        
        return '';
    }

    protected function getAllTopics(): array
    {
        return $this->topicList->getTopics();
    }

    protected function getConsumerTopicMap(): array
    {
        $result = [];
        
        try {
            $connection = $this->resourceConnection->getConnection();
            $tableNames = $connection->listTables();
            
            if (in_array($this->resourceConnection->getTableName('queue_consumer_link'), $tableNames)) {
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
        } catch (\Exception $e) {
            
        }
        
        try {
            if (in_array($this->resourceConnection->getTableName('queue_message_status'), $tableNames)) {
                $queueMessageStatusTable = $this->resourceConnection->getTableName('queue_message_status');
                
                $queueMessageData = $connection->fetchAll("
                    SELECT DISTINCT consumer_id, topic_name 
                    FROM {$queueMessageStatusTable}
                ");
                
                foreach ($queueMessageData as $row) {
                    if (!isset($result[$row['consumer_id']])) {
                        $result[$row['consumer_id']] = $row['topic_name'];
                    }
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
        
        $consumers = $this->consumerConfig->getConsumers();
        foreach ($consumers as $consumer) {
            $consumerName = $consumer->getName();
            if (!isset($result[$consumerName])) {
                $result[$consumerName] = $this->findTopicByHandler($consumer);
            }
        }
        
        ksort($result);
        return $result;
    }
} 