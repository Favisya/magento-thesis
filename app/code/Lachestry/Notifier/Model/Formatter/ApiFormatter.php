<?php

declare(strict_types=1);

namespace Lachestry\Notifier\Model\Formatter;

/**
 * Formatter for REST API errors
 */
class ApiFormatter extends AbstractSourceFormatter
{
    /**
     * @var string
     */
    protected string $title = '*REST API ERROR*';
    
    /**
     * @inheritdoc
     */
    public function format(array $additionalData): string
    {
        $info = '';
        
        if (isset($additionalData['http_code'])) {
            $info .= "📋 HTTP Code: {$additionalData['http_code']}\n";
        }
        
        return $info;
    }
}
