<?php
declare(strict_types=1);

namespace Lachestry\CronMonitoring\Model\ResourceModel;

use Lachestry\CronMonitoring\Model\Source\CronGroupsSource;
use Magento\Cron\Model\ResourceModel\Schedule as ScheduleResource;
use Magento\Framework\Model\ResourceModel\Db\Context;

class GroupSchedule extends ScheduleResource
{
    const ID = 'schedule_id';

    protected string $group = '';
    protected CronGroupsSource $groupsSource;

    public function __construct(
        Context $context,
        CronGroupsSource $groupsSource,
        $connectionName = null
    ) {
        $this->groupsSource = $groupsSource;
        parent::__construct($context, $connectionName);
    }

    public function setGroup(string $groupName): string
    {
        if ($this->validateGroup($groupName)) {
            $this->group = $groupName;
        }

        return $this->initGroupTable();
    }

    protected function initGroupTable(): string
    {
        $table = $this->getScheduleTableByGroupId($this->group);
        $this->_init($table, self::ID);

        return $table;
    }

    protected function validateGroup(string $groupName): bool
    {
        $allGroups = $this->groupsSource->toArray();
        return in_array($groupName, $allGroups);
    }
}
