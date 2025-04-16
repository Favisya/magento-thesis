<?php

declare(strict_types=1);

namespace Lachestry\RabbitMQMonitor\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Magento\Framework\MessageQueue\PublisherInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\MessageQueue\Consumer\ConfigInterface as ConsumerConfigInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\MessageQueue\Topology\ConfigInterface as TopologyConfigInterface;
use Magento\Framework\App\DeploymentConfig;
use Lachestry\RabbitMQMonitor\Model\TopicList;
use Magento\Framework\Console\Cli;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Magento\AsynchronousOperations\Api\Data\OperationInterfaceFactory;
use Magento\AsynchronousOperations\Api\Data\BulkSummaryInterfaceFactory;
use Magento\Framework\DataObject\IdentityGeneratorInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\MessageQueue\MessageValidator;
use Magento\Framework\Reflection\TypeProcessor;
use Magento\Framework\Bulk\OperationInterface;

class TestQueueMessage extends Command
{
    const ARGUMENT_TOPIC = 'topic';
    const OPTION_MESSAGE = 'message';
    const OPTION_CONSUMER = 'consumer';
    const OPTION_LIST_TOPICS = 'list-topics';
    const OPTION_CONTENT_TYPE = 'content-type';
    const OPTION_ENTITY_ID = 'entity-id';
    const OPTION_META_INFO = 'meta-info';

    protected PublisherInterface $publisher;
    protected SerializerInterface $serializer;
    protected ConsumerConfigInterface $consumerConfig;
    protected ResourceConnection $resourceConnection;
    protected TopologyConfigInterface $topologyConfig;
    protected DeploymentConfig $deploymentConfig;
    protected TopicList $topicList;
    protected State $appState;
    protected ?OperationInterfaceFactory $operationFactory;
    protected ?BulkSummaryInterfaceFactory $bulkSummaryFactory;
    protected ?IdentityGeneratorInterface $identityService;
    protected ?MessageValidator $messageValidator;
    protected ?TypeProcessor $typeProcessor;

    public function __construct(
        PublisherInterface $publisher,
        SerializerInterface $serializer,
        ConsumerConfigInterface $consumerConfig,
        ResourceConnection $resourceConnection,
        TopologyConfigInterface $topologyConfig,
        DeploymentConfig $deploymentConfig,
        TopicList $topicList,
        State $appState,
        OperationInterfaceFactory $operationFactory = null,
        BulkSummaryInterfaceFactory $bulkSummaryFactory = null,
        IdentityGeneratorInterface $identityService = null,
        MessageValidator $messageValidator = null,
        TypeProcessor $typeProcessor = null,
        string $name = null
    ) {
        $this->publisher = $publisher;
        $this->serializer = $serializer;
        $this->consumerConfig = $consumerConfig;
        $this->resourceConnection = $resourceConnection;
        $this->topologyConfig = $topologyConfig;
        $this->deploymentConfig = $deploymentConfig;
        $this->topicList = $topicList;
        $this->appState = $appState;
        $this->operationFactory = $operationFactory;
        $this->bulkSummaryFactory = $bulkSummaryFactory;
        $this->identityService = $identityService;
        $this->messageValidator = $messageValidator;
        $this->typeProcessor = $typeProcessor;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setName('rabbitmq:test:message')
            ->setDescription('Test sending message to RabbitMQ queue')
            ->addArgument(
                self::ARGUMENT_TOPIC,
                InputArgument::OPTIONAL,
                'The topic to publish to'
            )
            ->addOption(
                self::OPTION_MESSAGE,
                'm',
                InputOption::VALUE_REQUIRED,
                'The message to be sent',
                'Test message from RabbitMQ Monitor extension'
            )
            ->addOption(
                self::OPTION_CONSUMER,
                'c',
                InputOption::VALUE_REQUIRED,
                'Send message to topic associated with specified consumer'
            )
            ->addOption(
                self::OPTION_LIST_TOPICS,
                'l',
                InputOption::VALUE_NONE,
                'List available topics and consumers'
            )
            ->addOption(
                self::OPTION_CONTENT_TYPE,
                't',
                InputOption::VALUE_REQUIRED,
                'Content type (string, json, or raw)',
                'string'
            )
            ->addOption(
                self::OPTION_ENTITY_ID,
                'e',
                InputOption::VALUE_REQUIRED,
                'Entity ID for async operations',
                '1'
            )
            ->addOption(
                self::OPTION_META_INFO,
                'i',
                InputOption::VALUE_REQUIRED,
                'Meta information for async operations',
                '{}'
            );
        
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->appState->setAreaCode('global');
        } catch (LocalizedException $e) {
            // Область может быть уже установлена
        }
        
        if ($input->getOption(self::OPTION_LIST_TOPICS)) {
            $this->listTopicsAndConsumers($output);
            return Cli::RETURN_SUCCESS;
        }
        
        $topic = $input->getArgument(self::ARGUMENT_TOPIC);
        $consumerName = $input->getOption(self::OPTION_CONSUMER);
        
        if (!$topic && !$consumerName) {
            $output->writeln(
                '<error>You must specify either a topic name or a consumer name</error>'
            );
            $output->writeln(
                '<info>Use the --list-topics option to see available topics and consumers</info>'
            );
            return Cli::RETURN_FAILURE;
        }
        
        if (!$topic && $consumerName) {
            $topic = $this->getTopicForConsumer($consumerName);
            
            if (!$topic) {
                $output->writeln(
                    "<error>Could not find topic for consumer '{$consumerName}'</error>"
                );
                $output->writeln(
                    '<info>Use the --list-topics option to see available topics and consumers</info>'
                );
                return Cli::RETURN_FAILURE;
            }
            
            $output->writeln(
                "<info>Using topic '{$topic}' for consumer '{$consumerName}'</info>"
            );
        }
        
        $message = $input->getOption(self::OPTION_MESSAGE);
        $contentType = strtolower($input->getOption(self::OPTION_CONTENT_TYPE));
        $entityId = $input->getOption(self::OPTION_ENTITY_ID);
        $metaInfo = $input->getOption(self::OPTION_META_INFO);
        
        try {
            // Пытаемся определить требуемый тип сообщения для топика
            $messageType = $this->getRequiredMessageType($topic);
            $output->writeln("<info>Detected required message type: {$messageType}</info>");
            
            // Готовим сообщение в зависимости от типа
            $processedMessage = $this->prepareMessageForTopic(
                $topic,
                $message,
                $contentType,
                $entityId,
                $metaInfo,
                $messageType
            );
            
            $this->publisher->publish($topic, $processedMessage);
            $output->writeln(
                "<info>Message sent successfully to topic '{$topic}'</info>"
            );
            
            // Выводим информацию о сообщении
            $output->writeln("<info>Message type: " . gettype($processedMessage) . "</info>");
            if (is_object($processedMessage)) {
                $output->writeln("<info>Message class: " . get_class($processedMessage) . "</info>");
            }
            
            return Cli::RETURN_SUCCESS;
        } catch (\Exception $e) {
            $output->writeln(
                "<error>Error sending message to topic '{$topic}': " . $e->getMessage() . "</error>"
            );
            return Cli::RETURN_FAILURE;
        }
    }
    
    protected function getRequiredMessageType(string $topic): ?string
    {
        try {
            if (!$this->messageValidator || !$this->typeProcessor) {
                $objectManager = ObjectManager::getInstance();
                $this->messageValidator = $objectManager->get(MessageValidator::class);
                $this->typeProcessor = $objectManager->get(TypeProcessor::class);
            }
            
            // Для асинхронных операций и product_action_attribute
            if (            strpos($topic, 'async.') === 0 ||
                strpos($topic, 'async.V1.') === 0 ||
                strpos($topic, 'product_action_attribute.') === 0
            ) {
                return \Magento\AsynchronousOperations\Api\Data\OperationInterface::class;
            }
            
            // Получаем информацию о топике из валидатора сообщений
            $property = new \ReflectionProperty($this->messageValidator, 'topicMessageMapping');
            $property->setAccessible(true);
            $topicMessageMapping = $property->getValue($this->messageValidator);
            
            if (isset($topicMessageMapping[$topic])) {
                return $topicMessageMapping[$topic];
            }
            
            return null;
        } catch (\Exception $e) {
            return null;
        }
    }
    
    protected function prepareMessageForTopic(
        string $topic,
        $message,
        string $contentType,
        $entityId,
        $metaInfo,
        ?string $messageType
    ) {
        // Обрабатываем асинхронные операции
        if (            $messageType === \Magento\AsynchronousOperations\Api\Data\OperationInterface::class
            || is_subclass_of($messageType, \Magento\AsynchronousOperations\Api\Data\OperationInterface::class)
        ) {
            return $this->createAsyncOperationMessage($topic, $message, $entityId, $metaInfo);
        }
        
        // Для обычных сообщений используем стандартную обработку
        return $this->processMessage($message, $contentType);
    }
    
    protected function createAsyncOperationMessage(string $topic, $message, $entityId, $metaInfo)
    {
        if (!$this->operationFactory) {
            $objectManager = ObjectManager::getInstance();
            $this->operationFactory = $objectManager->get(OperationInterfaceFactory::class);
            $this->bulkSummaryFactory = $objectManager->get(BulkSummaryInterfaceFactory::class);
            $this->identityService = $objectManager->get(IdentityGeneratorInterface::class);
        }
        
        // Формируем метаданные операции
        try {
            $metaInfoArray = json_decode($metaInfo, true);
        } catch (\Exception $e) {
            $metaInfoArray = [];
        }
        
        if (!is_array($metaInfoArray)) {
            $metaInfoArray = [];
        }
        
        // Создаем идентификаторы для операции и пакета
        $operationId = $this->identityService->generateId();
        $bulkUuid = $this->identityService->generateId();
        
        // Формируем данные для операции
        if (is_string($message) && json_decode($message) === null) {
            $serializedData = $this->serializer->serialize([
                'entity_id' => $entityId,
                'message' => $message
            ]);
        } else {
            // Если передали JSON, используем его
            $serializedData = is_string($message) ? $message : $this->serializer->serialize($message);
        }
        
        // Создаем объект операции
        $operation = $this->operationFactory->create();
        $operation->setBulkUuid($bulkUuid)
            ->setTopicName($topic)
            ->setSerializedData($serializedData)
            ->setStatus(OperationInterface::STATUS_TYPE_OPEN)
            ->setOperationId($operationId)
            ->setResultMessage('')
            ->setErrorCode(null);
        
        // Создаем метаданные для операции, если не указаны
        if (!isset($metaInfoArray['user_id'])) {
            $metaInfoArray['user_id'] = UserContextInterface::USER_TYPE_ADMIN;
        }
        if (!isset($metaInfoArray['meta_information'])) {
            $metaInfoArray['meta_information'] = 'Test operation created by RabbitMQ Monitor extension';
        }
        
        // Устанавливаем метаданные
        $operation->setMetadata($this->serializer->serialize($metaInfoArray));
        
        return $operation;
    }
    
    protected function processMessage($message, string $contentType)
    {
        switch ($contentType) {
            case 'json':
                if (is_string($message)) {
                    return $this->serializer->unserialize($message);
                }
                return $message;
            
            case 'raw':
                return $message;
            
            case 'string':
            default:
                if (is_array($message)) {
                    return $this->serializer->serialize($message);
                }
                return (string)$message;
        }
    }
    
    protected function listTopicsAndConsumers(OutputInterface $output)
    {
        $topics = $this->getAllTopics();
        $consumerTopicMap = $this->getConsumerTopicMap();
        
        $output->writeln('<info>Available Topics:</info>');
        foreach ($topics as $topic) {
            $messageType = $this->getRequiredMessageType($topic) ?: 'unknown';
            $output->writeln("  - {$topic} (type: {$messageType})");
        }
        
        $output->writeln('');
        $output->writeln('<info>Available Consumers and their Topics:</info>');
        foreach ($consumerTopicMap as $consumer => $topic) {
            $output->writeln("  - {$consumer} -> {$topic}");
        }
    }
    
    protected function getTopicForConsumer(string $consumerName): ?string
    {
        return $this->topicList->getTopicForConsumer($consumerName);
    }
    
    protected function findConsumerByName(string $name)
    {
        foreach ($this->consumerConfig->getConsumers() as $consumer) {
            if ($consumer->getName() === $name) {
                return $consumer;
            }
        }
        
        return null;
    }
    
    protected function findTopicByHandler(string $handler): ?string
    {
        $crontabConsumersConfig = $this->deploymentConfig->get('crontab') ?? [];
        foreach ($crontabConsumersConfig as $jobCode => $jobConfig) {
            if (
            isset($jobConfig['instance'], $jobConfig['method']) &&
                $jobConfig['instance'] === 'Magento\MessageQueue\Model\Cron\ConsumersRunner' &&
                $jobConfig['method'] === 'run'
            ) {
                if (
                isset($jobConfig['arguments']['consumerId']['value']) &&
                    $jobConfig['arguments']['consumerId']['value'] === $handler
                ) {
                    if (
                    isset($jobConfig['arguments']['consumerOptions']['value']) &&
                        isset($jobConfig['arguments']['consumerOptions']['value']['topics']) &&
                        !empty($jobConfig['arguments']['consumerOptions']['value']['topics'])
                    ) {
                        return $jobConfig['arguments']['consumerOptions']['value']['topics'][0];
                    }
                }
            }
        }
        
        return null;
    }
    
    protected function getAllTopics(): array
    {
        return $this->topicList->getTopics();
    }
    
    protected function getConsumerTopicMap(): array
    {
        return $this->topicList->getConsumerTopicMap();
    }
}
