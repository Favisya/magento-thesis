<?php
declare(strict_types=1);

namespace Lachestry\Notifier\Model\Formatter;

class StuckCronFormatter extends AbstractSourceFormatter
{
    protected string $title = '*STUCK CRON JOB*';
    
    public function format(array $additionalData): string
    {
        $info = '';
        
        if (isset($additionalData['job_code'])) {
            $info .= "📋 Job: {$additionalData['job_code']}\n";
            
            if (isset($additionalData['executed_at'])) {
                $info .= "⏱️ Running since: {$additionalData['executed_at']}\n";
            }
            
            if (isset($additionalData['group'])) {
                $info .= "🔄 Group: {$additionalData['group']}\n";
            }
            
            if (isset($additionalData['threshold'])) {
                $info .= "⏳ Threshold: {$additionalData['threshold']} minutes\n";
            }
            
            if (isset($additionalData['runtime'])) {
                $info .= "⌛ Current runtime: {$additionalData['runtime']} minutes\n";
            }
        }
        
        return $info;
    }
} 