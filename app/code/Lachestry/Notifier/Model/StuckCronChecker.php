<?php

declare(strict_types=1);

namespace Lachestry\Notifier\Model;

use Lachestry\CronMonitoring\Model\Config as CronMonitoringConfig;
use Magento\Cron\Model\ResourceModel\Schedule\Collection as ScheduleCollection;
use Magento\Cron\Model\ResourceModel\Schedule\CollectionFactory as ScheduleCollectionFactory;
use Magento\Cron\Model\Schedule;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class StuckCronChecker
{
    public function __construct(
        private readonly ScheduleCollectionFactory $scheduleCollectionFactory,
        private readonly CronMonitoringConfig      $cronMonitoringConfig,
        private readonly DateTime                  $dateTime,
        private readonly ErrorHandler              $errorHandler,
        private readonly LoggerInterface           $logger,
        private readonly ScopeConfigInterface      $scopeConfig,
    ) {
    }

    public function execute(): void
    {
        try {
            $scheduleCollection = $this->getRunningJobs();
            $cronGroupTimeMap   = $this->cronMonitoringConfig->getCronGroupTimeMap();

            foreach ($scheduleCollection as $schedule) {
                $jobCode   = $schedule->getJobCode();
                $cronGroup = $this->getCronGroupForJob($jobCode);

                if (!isset($cronGroupTimeMap[$cronGroup])) {
                    continue;
                }

                $threshold  = (int) $cronGroupTimeMap[$cronGroup]['time_before'];
                $executedAt = $schedule->getExecutedAt();

                if (!$executedAt) {
                    continue;
                }

                $executedTime = strtotime($executedAt);
                $currentTime  = $this->dateTime->gmtTimestamp();
                $runTime      = ($currentTime - $executedTime) / 60;

                if ($runTime > $threshold) {
                    $this->notifyAboutStuckCron($schedule, $cronGroup, $threshold, $runTime);
                }
            }
        } catch (\Throwable $e) {
            $this->logger->error('Error checking for stuck cron jobs: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    private function notifyAboutStuckCron(
        Schedule $schedule,
        string   $group,
        int      $threshold,
        float    $runtime,
    ): void {
        $jobCode    = $schedule->getJobCode();
        $executedAt = $schedule->getExecutedAt();

        $exception = new \Exception(sprintf(
            'Cron job "%s" from group "%s" is stuck. Running for %.2f minutes (threshold: %d minutes)',
            $jobCode,
            $group,
            $runtime,
            $threshold
        ));

        $this->errorHandler->handleError($exception, 'stuck_cron', [
            'job_code'    => $jobCode,
            'executed_at' => $executedAt,
            'group'       => $group,
            'threshold'   => $threshold,
            'runtime'     => round($runtime, 2),
        ]);
    }

    private function getRunningJobs(): ScheduleCollection
    {
        $collection = $this->scheduleCollectionFactory->create();
        $collection->addFieldToFilter('status', Schedule::STATUS_RUNNING);
        return $collection;
    }

    private function getCronGroupForJob(string $jobCode): string
    {
        $defaultGroup = 'default';

        $cronConfig = $this->scopeConfig->getValue('crontab');

        if (!is_array($cronConfig)) {
            return $defaultGroup;
        }

        foreach ($cronConfig as $groupName => $groupConfig) {
            if (!isset($groupConfig['jobs'])) {
                continue;
            }

            if (isset($groupConfig['jobs'][$jobCode])) {
                return $groupName;
            }
        }

        return $defaultGroup;
    }
}
