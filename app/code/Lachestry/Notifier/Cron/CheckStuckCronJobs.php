<?php

declare(strict_types=1);

namespace Lachestry\Notifier\Cron;

use Lachestry\Notifier\Model\StuckCronChecker;
use Lachestry\Notifier\Model\Config;

class CheckStuckCronJobs
{
    public function __construct(
        private readonly StuckCronChecker $stuckCronChecker,
        private readonly Config $config
    ) {
    }

    public function execute(): void
    {
        if (!$this->config->isStuckCronNotificationEnabled()) {
            return;
        }
        
        $this->stuckCronChecker->execute();
    }
}
