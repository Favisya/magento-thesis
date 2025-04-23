<?php

declare(strict_types=1);

namespace Lachestry\Notifier\Model\Cron;

/**
 * Расширение класса Schedule для явного объявления метода setStatus как публичного
 */
class Schedule extends \Magento\Cron\Model\Schedule
{
    /**
     * Явное объявление метода setStatus как публичного
     *
     * @param string $status
     * @return $this
     */
    public function setStatus(string $value): \Magento\Cron\Model\Schedule
    {
        return parent::setStatus($value);
    }
}
