<?php
declare(strict_types=1);

namespace Lachestry\RabbitMQMonitor\Ui\DataProvider;

use Magento\Framework\Api\Filter;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\ReportingInterface;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\MessageQueue\Consumer\ConfigInterface as ConsumerConfigInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider;
use Magento\MessageQueue\Model\Cron\ConsumersRunner;
use Lachestry\RabbitMQMonitor\Model\ConsumerActivityManager;
use Lachestry\RabbitMQMonitor\Model\TopicList;

class ConsumersDataProvider extends DataProvider
{
    private const STATUS_RUNNING = 'Running';
    private const STATUS_DISABLED = 'Disabled';
    private const STATUS_STOPPED = 'Stopped';

    private const STATUS_CLASSES = [
        self::STATUS_RUNNING  => 'grid-severity-notice',
        self::STATUS_DISABLED => 'grid-severity-minor',
        self::STATUS_STOPPED  => 'grid-severity-critical',
    ];

    protected ConsumerActivityManager $consumerActivityManager;
    protected ConsumerConfigInterface $consumerConfig;
    protected DeploymentConfig $deploymentConfig;
    protected ConsumersRunner $consumersRunner;
    protected TopicList $topicList;
    private array $loadedData = [];
    private array $cachedItems = [];

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        ReportingInterface $reporting,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        RequestInterface $request,
        FilterBuilder $filterBuilder,
        ConsumerActivityManager $consumerActivityManager,
        ConsumerConfigInterface $consumerConfig,
        DeploymentConfig $deploymentConfig,
        ConsumersRunner $consumersRunner,
        TopicList $topicList,
        array $meta = [],
        array $data = [],
    ) {
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $reporting,
            $searchCriteriaBuilder,
            $request,
            $filterBuilder,
            $meta,
            $data
        );

        $this->consumerActivityManager = $consumerActivityManager;
        $this->consumerConfig          = $consumerConfig;
        $this->deploymentConfig        = $deploymentConfig;
        $this->consumersRunner         = $consumersRunner;
        $this->topicList               = $topicList;
    }

    public function getData()
    {
        if (!empty($this->loadedData)) {
            return $this->loadedData;
        }

        $items = $this->getItems();

        $this->loadedData = [
            'totalRecords' => count($items),
            'items'        => $items,
        ];

        return $this->loadedData;
    }

    protected function getItems(): array
    {
        if (!empty($this->cachedItems)) {
            return $this->cachedItems;
        }

        $allowedConsumers = $this->getAllowedConsumers();
        $consumerConfig   = $this->consumerConfig->getConsumers();
        $runningConsumers = $this->consumerActivityManager->getRunningConsumers();
        $consumerTopicMap = $this->topicList->getConsumerTopicMap();

        $items = [];

        foreach ($consumerConfig as $consumer) {
            $consumerName = $consumer->getName();
            $isAllowed    = empty($allowedConsumers) || in_array($consumerName, $allowedConsumers);
            $isRunning    = array_key_exists($consumerName, $runningConsumers);

            $status = $this->determineConsumerStatus($consumerName, $isRunning, $isAllowed);

            $consumerData = [
                'id'            => $consumerName,
                'name'          => $consumerName,
                'connection'    => $consumer->getConnection(),
                'queue'         => $consumer->getQueue(),
                'topic'         => $consumerTopicMap[$consumerName] ?? '-',
                'status'        => $status,
                'pid'           => '-',
                'last_activity' => '-',
            ];

            if ($isRunning) {
                $activityData        = $runningConsumers[$consumerName];
                $consumerData['pid'] = $activityData->getPid();
                $lastActivity        = $activityData->getLastActivity();

                if ($lastActivity) {
                    $consumerData['last_activity'] = $this->formatCustomDate($lastActivity);
                }
            }

            $items[] = $consumerData;
        }

        usort($items, function ($a, $b) {
            return strcmp($a['name'], $b['name']);
        });

        $this->cachedItems = $items;
        return $items;
    }

    public function addFilter(Filter $filter)
    {
        return $this;
    }

    protected function determineConsumerStatus(
        string $consumerName,
        bool   $isRunning,
        bool   $isAllowed,
    ): string {
        if ($isRunning) {
            return self::STATUS_RUNNING;
        }

        if (!$isAllowed) {
            return self::STATUS_DISABLED;
        }

        return self::STATUS_STOPPED;
    }

    protected function getStatusClass(string $status): string
    {
        return self::STATUS_CLASSES[$status] ?? '';
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
