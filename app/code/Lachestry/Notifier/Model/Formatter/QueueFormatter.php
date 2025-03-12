<?php
declare(strict_types=1);

namespace Lachestry\Notifier\Model\Formatter;

/**
 * Formatter for message queue errors
 */
class QueueFormatter extends AbstractSourceFormatter
{
    /**
     * @var string
     */
    protected string $title = '*MESSAGE QUEUE ERROR*';
    
    /**
     * @inheritdoc
     */
    public function format(array $additionalData): string
    {
        $info = '';
        
        if (isset($additionalData['topic'])) {
            $info .= "📋 Topic: {$additionalData['topic']}\n";
            
            if (isset($additionalData['message_id'])) {
                $info .= "🆔 Message ID: {$additionalData['message_id']}\n";
            }
        }
        
        return $info;
    }
} 