<?php

declare(strict_types=1);

namespace Lachestry\Telegram\Model;

use Lachestry\Telegram\Api\Data\TelegramChatInterface;
use Lachestry\Telegram\Model\ResourceModel\TelegramChat\Collection;
use Lachestry\Telegram\Model\ResourceModel\TelegramChat\CollectionFactory;

class TelegramChatProvider
{
    public function __construct(
        protected readonly CollectionFactory $collectionFactory,
    ) {
    }

    /**
     * @return TelegramChat[]
     */
    public function getAllChats(): array
    {
        return $this->getCollection()->getItems();
    }

    /**
     * @return TelegramChat[]
     */
    public function getActiveChats(): array
    {
        return $this->getCollection()
            ->addFieldToFilter(TelegramChatInterface::IS_ACTIVE, ['eq' => 1])
            ->getItems();
    }

    protected function getCollection(): Collection
    {
        return $this->collectionFactory->create();
    }
}
