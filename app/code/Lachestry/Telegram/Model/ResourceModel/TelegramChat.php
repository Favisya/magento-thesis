<?php

declare(strict_types=1);

namespace Lachestry\Telegram\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Lachestry\Telegram\Api\Data\TelegramChatInterface as TgChInterface;

class TelegramChat extends AbstractDb
{
    const TABLE = 'lachestry_telegram_chats';

    public function __construct(
        Context $context,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
    }

    public function _construct()
    {
        $this->_init(self::TABLE, 'id');
    }

    public function saveChats(array $records): void
    {
        $allChatIds = $this->getAllChatIds();
        foreach ($records as $key => $record) {
            if (in_array($record[TgChInterface::CHAT_ID], $allChatIds)) {
                unset($records[$key]);
            }
        }

        if (empty($records)) {
            return;
        }

        $this->getConnection()->insertMultiple(self::TABLE, $records);
    }

    public function deleteChats(array $ids): void
    {
        $allChatIds = $this->getAllChatIds();
        foreach ($ids as $key => $chatId) {
            if (!in_array($chatId[TgChInterface::CHAT_ID] ?? '', $allChatIds)) {
                unset($ids[$key]);
            }
        }

        if (empty($ids)) {
            return;
        }

        $this->getConnection()->delete(self::TABLE, ['chat_id' => $ids]);
    }

    public function getAllChatIds(): array
    {
        $connection = $this->getConnection();
        $query = $connection->select()->from(self::TABLE, 'chat_id');

        return $connection->fetchCol($query);
    }
}
