<?php
declare(strict_types=1);

namespace Lachestry\Notifier\Plugin\Queue;

use Magento\Framework\MessageQueue\ConsumerInterface;
use Magento\Framework\MessageQueue\EnvelopeInterface;
use Lachestry\Notifier\Model\ErrorHandler;
use Lachestry\Notifier\Model\Config;

class NotifyQueueErrors
{
    /**
     * @param ErrorHandler $errorHandler
     * @param Config $config
     */
    public function __construct(
        protected readonly ErrorHandler $errorHandler,
        protected readonly Config $config
    ) {}

    /**
     * Intercept message queue processing to catch errors
     *
     * @param ConsumerInterface $subject
     * @param callable $proceed
     * @param EnvelopeInterface $envelope
     * @return void
     */
    public function aroundProcess(
        ConsumerInterface $subject,
        callable $proceed,
        EnvelopeInterface $envelope
    ): void {
        if (!$this->config->isQueueNotificationEnabled()) {
            $proceed($envelope);
            return;
        }

        try {
            $proceed($envelope);
        } catch (\Throwable $e) {
            $topic = $envelope->getProperties()['topic'] ?? 'unknown';
            $messageId = $envelope->getMessageId();
            
            $this->errorHandler->handleError($e, 'message_queue', [
                'topic' => $topic,
                'message_id' => $messageId
            ]);
            
            throw $e;
        }
    }
} 