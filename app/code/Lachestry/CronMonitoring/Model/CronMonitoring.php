<?php
declare(strict_types=1);

namespace Lachestry\CronMonitoring\Model;

use Lachestry\Configuration\Model\Config;
use Magento\Cron\Model\ResourceModel\Schedule\CollectionFactory as ScheduleCollectionFactory;
use Magento\Cron\Model\Schedule;
use Magento\Framework\Stdlib\DateTime\DateTime;

class CronMonitoring
{
    private Config $config;
    private ScheduleCollectionFactory $scheduleCollectionFactory;

    public function __construct(
        Config                    $config,
        ScheduleCollectionFactory $scheduleCollectionFactory,
    ) {
        $this->config                    = $config;
        $this->scheduleCollectionFactory = $scheduleCollectionFactory;
    }

    public function checkCronStatus(): array
    {
        $monitorPeriod = (int) $this->config->getConfigValue('lachestry/cron/monitor_period') ?: 24;

        $collection = $this->scheduleCollectionFactory->create();
        $collection->addFieldToFilter('scheduled_at', ['gteq' => date('Y-m-d H:i:s', strtotime("-{$monitorPeriod} hours"))]);

        $result = [];
        foreach ($collection->getItems() as $schedule) {
            $result[] = [
                'job_code'     => $schedule->getJobCode(),
                'status'       => $schedule->getStatus(),
                'message'      => $schedule->getMessages(),
                'scheduled_at' => $schedule->getScheduledAt(),
            ];
        }

        return $result;
    }

    public function getFailedJobs(): array
    {
        $monitorPeriod = (int) $this->config->getConfigValue('lachestry/cron/monitor_period') ?: 24;

        $collection = $this->scheduleCollectionFactory->create();
        $collection->addFieldToFilter('scheduled_at', ['gteq' => date('Y-m-d H:i:s', strtotime("-{$monitorPeriod} hours"))]);
        $collection->addFieldToFilter('status', ['in' => [Schedule::STATUS_ERROR, Schedule::STATUS_MISSED]]);

        $result = [];
        foreach ($collection->getItems() as $schedule) {
            $result[] = [
                'job_code'     => $schedule->getJobCode(),
                'status'       => $schedule->getStatus(),
                'message'      => $schedule->getMessages(),
                'scheduled_at' => $schedule->getScheduledAt(),
            ];
        }

        return $result;
    }
}
