<?php
declare(strict_types=1);

namespace Lachestry\Notifier\Model\Formatter;

/**
 * Formatter for cron job errors
 */
class CronFormatter extends AbstractSourceFormatter
{
    /**
     * @var string
     */
    protected string $title = '*CRON JOB ERROR*';
    
    /**
     * @inheritdoc
     */
    public function format(array $additionalData): string
    {
        $info = '';
        
        if (isset($additionalData['job_code'])) {
            $info .= "📋 Job: {$additionalData['job_code']}\n";
            
            if (isset($additionalData['scheduled_at'])) {
                $info .= "📅 Scheduled at: {$additionalData['scheduled_at']}\n";
            }
        }
        
        return $info;
    }
} 