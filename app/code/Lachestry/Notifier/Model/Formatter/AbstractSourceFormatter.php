<?php
declare(strict_types=1);

namespace Lachestry\Notifier\Model\Formatter;

use Lachestry\Notifier\Api\Formatter\SourceFormatterInterface;

/**
 * Abstract base class for source formatters
 */
abstract class AbstractSourceFormatter implements SourceFormatterInterface
{
    /**
     * @var string
     */
    protected string $title;
    
    /**
     * @inheritdoc
     */
    public function getTitle(): string
    {
        return $this->title;
    }
} 