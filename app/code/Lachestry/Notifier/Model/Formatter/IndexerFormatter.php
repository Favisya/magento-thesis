<?php
declare(strict_types=1);

namespace Lachestry\Notifier\Model\Formatter;

/**
 * Formatter for indexer errors
 */
class IndexerFormatter extends AbstractSourceFormatter
{
    /**
     * @var string
     */
    protected string $title = '*INDEXER ERROR*';
    
    /**
     * @inheritdoc
     */
    public function format(array $additionalData): string
    {
        $info = '';
        
        if (isset($additionalData['indexer'])) {
            $info .= "📋 Indexer: {$additionalData['indexer']}\n";
            
            if (isset($additionalData['title'])) {
                $info .= "📝 Title: {$additionalData['title']}\n";
            }
        }
        
        return $info;
    }
} 