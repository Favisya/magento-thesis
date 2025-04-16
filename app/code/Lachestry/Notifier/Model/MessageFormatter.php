<?php

declare(strict_types=1);

namespace Lachestry\Notifier\Model;

use Lachestry\Notifier\Api\Formatter\SourceFormatterInterface;
use Lachestry\Notifier\Model\Formatter\DefaultFormatter;

/**
 * Formats error messages for notifications
 */
class MessageFormatter
{
    /**
     * @var array<string, SourceFormatterInterface>
     */
    private array $formatters = [];

    /**
     * @var SourceFormatterInterface
     */
    private SourceFormatterInterface $defaultFormatter;

    /**
     * @param DefaultFormatter $defaultFormatter
     * @param array $formatters Associative array of formatters: ['source' => FormatterInstance]
     */
    public function __construct(
        DefaultFormatter $defaultFormatter,
        array            $formatters = [],
    ) {
        $this->defaultFormatter = $defaultFormatter;
        $this->formatters       = $formatters;
    }

    /**
     * Add a formatter for a specific source
     *
     * @param string $source
     * @param SourceFormatterInterface $formatter
     * @return $this
     */
    public function addFormatter(string $source, SourceFormatterInterface $formatter): self
    {
        $this->formatters[$source] = $formatter;
        return $this;
    }

    /**
     * Format error message for Telegram
     *
     * @param \Throwable $exception Exception or error
     * @param string $source Error source (cron, indexer, message_queue, etc.)
     * @param array $additionalData Additional data
     * @return string
     */
    public function formatTelegramMessage(
        \Throwable $exception,
        string     $source,
        array      $additionalData = [],
    ): string {
        $formatter = $this->formatters[$source] ?? $this->defaultFormatter;
        $title     = $formatter->getTitle();

        $message = "🔴 $title\n";
        $message .= "⏰ " . date('Y-m-d H:i:s') . "\n";
        $message .= "🔍 Source: $source\n";

        // Add source-specific information
        $message .= $formatter->format($additionalData);

        // Add exception details
        $message .= "❗ Error: " . $exception->getMessage() . "\n";
        $message .= "⚠️ File: " . $exception->getFile() . " (line " . $exception->getLine() . ")\n";

        // Add stack trace
        $trace      = explode("\n", $exception->getTraceAsString());
        $traceShort = array_slice($trace, 0, 3);
        $message    .= "🔍 Stack trace:\n```" . implode("\n", $traceShort) . "```\n";

        return $message;
    }
}
