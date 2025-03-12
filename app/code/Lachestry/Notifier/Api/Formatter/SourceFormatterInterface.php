<?php
declare(strict_types=1);

namespace Lachestry\Notifier\Api\Formatter;

/**
 * Interface for error source formatters
 */
interface SourceFormatterInterface
{
    /**
     * Format additional data specific to the error source
     *
     * @param array $additionalData Source-specific additional data
     * @return string Formatted message part
     */
    public function format(array $additionalData): string;
    
    /**
     * Get error title for this source
     *
     * @return string
     */
    public function getTitle(): string;
} 