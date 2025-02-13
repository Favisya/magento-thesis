<?php
declare(strict_types=1);

namespace Lachestry\Telegram\Model;

use Magento\Framework\Model\AbstractModel;
use Lachestry\Telegram\Api\Data\TelegramChatInterface;
use Lachestry\Telegram\Model\ResourceModel\TelegramChat as TelegramChatResource;

class TelegramChat extends AbstractModel implements TelegramChatInterface
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(TelegramChatResource::class);
        parent::_construct();
    }

    /**
     * @param int $id
     * @return TelegramChatInterface
     */
    public function setChatId(int $id): TelegramChatInterface
    {
        return $this->setData(self::CHAT_ID, $id);
    }

    /**
     * @return int
     */
    public function getChatId(): int
    {
        return $this->getData(self::CHAT_ID);
    }

    /**
     * @param string|null $chatName
     * @return TelegramChatInterface
     */
    public function setChatName(?string $chatName): TelegramChatInterface
    {
        return $this->setData(self::USER_NAME, $chatName);
    }

    /**
     * @return string|null
     */
    public function getChatName(): ?string
    {
        return $this->getData(self::CHAT_NAME);
    }

    /**
     * @param string|null $userName
     * @return TelegramChatInterface
     */
    public function setUserName(?string $userName): TelegramChatInterface
    {
        return $this->setData(self::USER_NAME, $userName);
    }

    /**
     * @return string|null
     */
    public function getUserName(): ?string
    {
        return $this->getData(self::USER_NAME);
    }

    /**
     * @param string|null $createdAt
     * @return TelegramChatInterface
     */
    public function setCreatedAt(?string $createdAt): TelegramChatInterface
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * @return string|null
     */
    public function getCreatedAt(): ?string
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * @param bool $flag
     * @return TelegramChatInterface
     */
    public function setIsActive(bool $flag): TelegramChatInterface
    {
        return $this->setData(self::IS_ACTIVE, $flag);
    }

    /**
     * @return bool
     */
    public function getIsActive(): bool
    {
        return $this->getData(self::IS_ACTIVE);
    }

    /**
     * @param string|null $updatedAt
     * @return TelegramChatInterface
     */
    public function setTelegramUpdatedAt(?string $updatedAt): TelegramChatInterface
    {
        return $this->setData(self::TELEGRAM_UPDATED_AT, $updatedAt);
    }

    /**
     * @return string|null
     */
    public function getTelegramUpdatedAt(): ?string
    {
        return $this->getData(self::TELEGRAM_UPDATED_AT);
    }
}
