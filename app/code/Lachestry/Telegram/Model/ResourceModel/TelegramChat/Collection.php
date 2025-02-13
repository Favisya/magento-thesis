<?php
declare(strict_types=1);

namespace Lachestry\Telegram\Model\ResourceModel\TelegramChat;

use Lachestry\Telegram\Model\TelegramChat;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Lachestry\Telegram\Model\ResourceModel\TelegramChat as TelegramChatResource;

class Collection extends AbstractCollection
{
    public function _construct()
    {
        $this->_init(TelegramChat::class, TelegramChatResource::class);
    }
}
