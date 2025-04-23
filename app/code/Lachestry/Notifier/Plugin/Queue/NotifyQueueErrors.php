<?php

declare(strict_types=1);

namespace Lachestry\Notifier\Plugin\Queue;

use Magento\Framework\MessageQueue\ConsumerInterface;
use Magento\Framework\MessageQueue\EnvelopeInterface;
use Lachestry\Notifier\Model\ErrorHandler;
use Lachestry\Notifier\Model\Config;

class NotifyQueueErrors
{
    public function __construct(
        protected readonly ErrorHandler $errorHandler,
        protected readonly Config       $config,
    ) {
    }

    /**
     * Intercept message queue processing to catch errors
     *
     * @param ConsumerInterface $subject
     * @param callable $proceed
     * @param EnvelopeInterface|string|null $envelope
     * @return void
     */
    public function aroundProcess(
        ConsumerInterface $subject,
        callable          $proceed,
                          $envelope = null,
    ): void {
        if (!$this->config->isQueueNotificationEnabled()) {
            $proceed($envelope);
            return;
        }

        try {
            $proceed($envelope);
        } catch (\Throwable $e) {
            $configuration = $subject->getConsumerConfiguration();
            if (!$configuration) {
                $this->errorHandler->handleError($e, 'message_queue', ['uknw','uknw','uknw']);
            }

            $queueName      = $configuration->getQueueName() ?? 'uknown';
            $connectionName = $configuration->getConsumerName();

            $topic = $configuration->getConsumerName() ?? 'unknown';
            $queueInfo = [
                'queue_name' => $queueName,
                'connection' => $connectionName ?? 'unknown',
            ];

            if ($envelope instanceof EnvelopeInterface) {
                $messageId = $envelope->getMessageId();
            }

            $errorData = [
                'topic'      => $topic,
                'message_id' => $messageId ?? 'unknown',
                'consumer'   => $topic,
            ];

            if (!empty($queueInfo)) {
                $errorData = array_merge($errorData, $queueInfo);
            }

            $this->errorHandler->handleError($e, 'message_queue', $errorData);

            throw $e;
        }
    }
}
