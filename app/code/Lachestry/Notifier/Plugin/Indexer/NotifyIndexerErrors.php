<?php

declare(strict_types=1);

namespace Lachestry\Notifier\Plugin\Indexer;

use Magento\Indexer\Model\Indexer;
use Lachestry\Notifier\Model\ErrorHandler;
use Lachestry\Notifier\Model\Config;

/**
 * Plugin to catch and notify about indexer errors
 */
class NotifyIndexerErrors
{
    /**
     * @param ErrorHandler $errorHandler
     * @param Config $config
     */
    public function __construct(
        protected readonly ErrorHandler $errorHandler,
        protected readonly Config $config
    ) {
    }

    /**
     * Intercept individual indexer execution and send notification on error
     *
     * @param Indexer $subject
     * @param callable $proceed
     * @return mixed
     */
    public function aroundReindexAll(
        Indexer $subject,
        callable $proceed
    ) {
        if (!$this->config->isIndexerNotificationEnabled()) {
            return $proceed();
        }

        try {
            return $proceed();
        } catch (\Throwable $e) {
            $indexerTitle = $subject->getTitle();
            $indexerId = $subject->getId();

            $this->errorHandler->handleError($e, 'indexer', [
                'action'  => 'reindex',
                'indexer' => $indexerId,
                'title'   => $indexerTitle
            ]);

            throw $e;
        }
    }

    /**
     * Process reindexing of specified entities
     *
     * @param Indexer $subject
     * @param callable $proceed
     * @param array $ids
     * @return mixed
     */
    public function aroundReindexList(
        Indexer $subject,
        callable $proceed,
        $ids
    ) {
        if (!$this->config->isIndexerNotificationEnabled()) {
            return $proceed($ids);
        }

        try {
            return $proceed($ids);
        } catch (\Throwable $e) {
            $indexerTitle = $subject->getTitle();
            $indexerId = $subject->getId();

            $this->errorHandler->handleError($e, 'indexer', [
                'action'  => 'reindex',
                'indexer' => $indexerId,
                'title'   => $indexerTitle
            ]);

            throw $e;
        }
    }

    /**
     * Process reindexing of a single row
     *
     * @param Indexer $subject
     * @param callable $proceed
     * @param mixed $id
     * @return mixed
     */
    public function aroundReindexRow(
        Indexer $subject,
        callable $proceed,
        $id
    ) {
        if (!$this->config->isIndexerNotificationEnabled()) {
            return $proceed($id);
        }

        try {
            return $proceed($id);
        } catch (\Throwable $e) {
            $indexerTitle = $subject->getTitle();
            $indexerId = $subject->getId();

            $this->errorHandler->handleError($e, 'indexer', [
                'action'  => 'reindex',
                'indexer' => $indexerId,
                'title'   => $indexerTitle
            ]);

            throw $e;
        }
    }
}
