<?php
declare(strict_types=1);

namespace Lachestry\CronMonitoring\Ui\Schedule;

use Lachestry\CronMonitoring\Api\CronGroupRepositoryInterface;
use Lachestry\CronMonitoring\Model\Config;
use Magento\Framework\App\Request\Http;
use Lachestry\CronMonitoring\Model\ResourceModel\GroupSchedule\Collection;
use Lachestry\CronMonitoring\Model\ResourceModel\GroupSchedule\CollectionFactory;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\Framework\UrlInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class DataProvider extends AbstractDataProvider
{
    protected const GROUP_PARAM = 'group';

    protected const STATUS_RUNNING = 'running';
    protected const STATUS_ERROR   = 'error';

    protected Http $httpHandler;
    protected CronGroupRepositoryInterface $cronGroupRepository;
    protected Config $config;
    protected array $statusMap;
    protected UrlInterface $urlHandler;
    protected TimezoneInterface $timezone;
    protected DateTime $dateTime;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        CronGroupRepositoryInterface $cronGroupRepository,
        Http $httpHandler,
        UrlInterface $urlHandler,
        Config $config,
        TimezoneInterface $timezone,
        DateTime $dateTime,
        array $statusMap = [],
        array $meta = [],
        array $data = []
    ) {
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $meta,
            $data
        );
        $this->config = $config;
        $this->statusMap = $statusMap;
        $this->urlHandler = $urlHandler;
        $this->httpHandler = $httpHandler;
        $this->collection = $collectionFactory->create();
        $this->cronGroupRepository = $cronGroupRepository;
        $this->timezone = $timezone;
        $this->dateTime = $dateTime;
    }

    public function getGroup(): string
    {
        return $this->httpHandler->getParam(self::GROUP_PARAM);
    }

    public function getData(): array
    {
        $this->prepareUpdateUrl();

        $group = $this->getGroup();
        
        $collection = $this->getCollection();
        
        if ($group) {
            $collection->addFieldToFilter('group', $group);
        }
        
        $items = $collection->toArray();

        foreach ($items['items'] as &$item) {
            $status = $item['status'];
            $startTime = $item['executed_at'];

            $item['status_class'] = $this->getStatusClass($status, $startTime);
        }

        return $items;
    }

    public function getCollection(): Collection
    {
        $group = $this->getGroup();
        if ($group) {
            $this->collection->initGroupTable($group);
        }
        
        return $this->collection;
    }

    protected function prepareUpdateUrl(): self
    {
        $updateUrl = $this->urlHandler->getUrl(
            'mui/index/render',
            [self::GROUP_PARAM => $this->getGroup()]
        );

        $this->data['config']['update_url'] = $updateUrl;

        return $this;
    }

    protected function getStuckThreshold(): ?int
    {
        $stuckThreshold  = $this->config->getCronGroupTimeMap();

        $groupName = $this->getGroup();
        if (in_array($groupName, array_keys($stuckThreshold))) {
            return (int) $stuckThreshold[$groupName]['time_before'];
        }

        return null;
    }

    protected function getStatusClass(string $status, ?string $startTime): string
    {
        $diffTime = $this->calculateTimeDiff($startTime);
        $stuckThreshold = $this->getStuckThreshold() ?? $diffTime;

        $isStuck = ($stuckThreshold < $diffTime) && $status == self::STATUS_RUNNING;

        return $isStuck ? $this->statusMap[self::STATUS_ERROR] : $this->statusMap[$status];
    }

    protected function calculateTimeDiff(?string $startTime): ?int
    {
        if (!$startTime) {
            return null;
        }

        $startDateTime = new \DateTime($startTime);
        $currentDateTime = new \DateTime();

        $interval = $currentDateTime->diff($startDateTime);
        $differenceInMinutes = ($interval->days * 24 * 60) + ($interval->h * 60) + $interval->i;

        return $differenceInMinutes;
    }
}
