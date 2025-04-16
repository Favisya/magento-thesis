<?php

declare(strict_types=1);

namespace Lachestry\CronMonitoring\Block\Adminhtml\Grid\Schedule;

use Magento\Framework\View\Element\Template;

class Message extends Template
{
    protected const MSG_CLASS      = 'cron-message__overflow';
    protected const MAX_MSG_LENGTH = 300;

    public function renderMessage(?string $message): ?string
    {
        if (strlen($message ?? '') < self::MAX_MSG_LENGTH) {
            return $message;
        }

        return '<p class="' . self::MSG_CLASS . '">' . $message . '</p>';
    }
}
