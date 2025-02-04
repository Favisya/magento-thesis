<?php
declare(strict_types=1);

namespace Lachestry\CronMonitoring\Model;

use Lachestry\CronMonitoring\Api\CronGroupRepositoryInterface;
use Magento\Cron\Model\Groups\Config\Data as CronGroups;
use Magento\Cron\Model\Config\Data as JobCodes;
use Psr\Log\LoggerInterface;

class CronGroupRepository implements CronGroupRepositoryInterface
{
    protected CronGroups $cronGroups;
    protected JobCodes $jobCodes;
    protected Config $config;
    protected LoggerInterface $logger;

    public function __construct(
        CronGroups      $cronGroups,
        Config          $config,
        LoggerInterface $logger,
        JobCodes        $jobCodes
    ) {
        $this->logger = $logger;
        $this->config = $config;
        $this->cronGroups = $cronGroups;
        $this->jobCodes = $jobCodes;
    }

    /**
     * @inheritdoc
     */
    public function getGroupsData()
    {
        try {
            $cronGroups = $this->cronGroups->get();

            $groupsData = [];
            foreach ($cronGroups as $groupName => $cronGroup) {
                $groupsData[$groupName] = $this->getGroupData($groupName);
            }

            return $groupsData;
        } catch (\Exception $e) {
            $this->logger->error('Something gone wrong!' . PHP_EOL . $e->getMessage());
        }
    }

    /**
     * @inheritdoc
     */
    public function getGroupData(string $groupName)
    {
        try {
            $jobCodes = $this->jobCodes->get($groupName);
            $settings = $this->cronGroups->getByGroupId($groupName);

            $groupsStuckThresholds = $this->config->getCronGroupTimeMap();
            $groupsStuckThreshold  = $groupsStuckThresholds[$groupName][self::TIME_BEFORE] ?? null;

            $settings[self::CRON_STUCK_THRESHOLD]['value'] = $groupsStuckThreshold;

            return [
                self::SETTINGS => $settings,
                self::CODES    => $jobCodes
            ];
        } catch (\Exception $e) {
            $this->logger->error('Something gone wrong!' . PHP_EOL . $e->getMessage());
        }
    }

    /**
     * @inheritdoc
     */
    public function getGroupNames(): array
    {
        return array_keys($this->cronGroups->get());
    }
}
