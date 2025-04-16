<?php

declare(strict_types=1);

namespace Lachestry\Notifier\Model\Formatter;

/**
 * Default formatter for unknown error sources
 */
class DefaultFormatter extends AbstractSourceFormatter
{
    /**
     * @var string
     */
    protected string $title = '*SYSTEM ERROR*';
    
    /**
     * @inheritdoc
     */
    public function format(array $additionalData): string
    {
        return '';
    }
}
