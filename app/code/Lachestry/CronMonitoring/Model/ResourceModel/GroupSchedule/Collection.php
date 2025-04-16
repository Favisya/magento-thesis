<?php

declare(strict_types=1);

namespace Lachestry\CronMonitoring\Model\ResourceModel\GroupSchedule;

use Lachestry\Cron\Model\ResourceModel\JobCodes;
use Magento\Cron\Model\ResourceModel\Schedule\Collection as ScheduleCollection;
use Lachestry\Cron\Model\ResourceModel\JobCodes\Collection as JobCodesCollection;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Lachestry\CronMonitoring\Model\ResourceModel\GroupSchedule;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Cron\Model\Schedule;
use Psr\Log\LoggerInterface;

class Collection extends ScheduleCollection
{
    protected JobCodesCollection $jobCodesCollection;
    protected GroupSchedule $groupSchedule;
    protected JobCodes $jobCodes;

    public function __construct(
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        GroupSchedule $groupScheduleResource,
        JobCodes $jobCodes,
        JobCodesCollection $jobCodesCollection,
        AdapterInterface $connection = null,
        AbstractDb $resource = null
    ) {
        $this->groupSchedule = $groupScheduleResource;
        $this->jobCodes = $jobCodes;
        $this->jobCodesCollection = $jobCodesCollection;
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $connection,
            $resource
        );
    }

    public function _construct()
    {
        $this->_init(Schedule::class, $this->groupSchedule);
    }

    public function initGroupTable(string $scheduleGroup): self
    {
        $table = $this->groupSchedule->setGroup($scheduleGroup);
        $this->_init(Schedule::class, $this->groupSchedule);
        $this->setMainTable($table);

        $this->jobCodesCollection->synchronizeJobCodes();
        $this->join(JobCodes::TABLE, 'job_code = job_code_name');

        return $this;
    }

    /**
     *  Set resource instance
     *
     * @param $model ResourceConnection|AbstractDb
     */
    public function setResourceModel($model): self
    {
        $this->_resource = $model;
        return $this;
    }

    /**
     * Get resource instance
     *
     * @return \Magento\Framework\Model\ResourceModel\Db\AbstractDb
     */
    public function getResource()
    {
        return $this->_resource ?? $this->groupSchedule;
    }

    /**
     * @param $model string
     * @param $resourceModel ResourceConnection|AbstractDb
     */
    protected function _init($model, $resourceModel): self
    {
        $this->setModel($model);
        $this->setResourceModel($resourceModel);
        return $this;
    }
}
