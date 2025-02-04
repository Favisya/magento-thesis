<?php

namespace Lachestry\CronMonitoring\Api;

interface CronGroupRepositoryInterface
{
    public const CODES       = 'codes';
    public const SETTINGS    = 'settings';
    public const TIME_BEFORE = 'time_before';

    public const CRON_STUCK_THRESHOLD = 'time_before_cron_stuck';

    /**
     * @return array
     */
    public function getGroupsData();

    /**
     * @return array
     */
    public function getGroupData(string $groupName);

    /**
     * @return array
     */
    public function getGroupNames(): array;
}
