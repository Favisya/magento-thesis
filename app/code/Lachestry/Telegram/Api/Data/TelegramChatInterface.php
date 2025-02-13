<?php

namespace Lachestry\Telegram\Api\Data;

interface TelegramChatInterface
{
    const CHAT_ID             = 'chat_id';
    const CHAT_NAME           = 'chat_name';
    const USER_NAME           = 'user_name';
    const CREATED_AT          = 'created_at';
    const IS_ACTIVE           = 'is_active';
    const TELEGRAM_UPDATED_AT = 'telegram_updated_at';

    /**
     * @param int $id
     * @return self
     */
    public function setChatId(int $id): self;

    /**
     * @return int
     */
    public function getChatId(): int;

    /**
     * @param string|null $chatName
     * @return self
     */
    public function setChatName(?string $chatName): self;

    /**
     * @return string|null
     */
    public function getChatName(): ?string;

    /**
     * @param string|null $userName
     * @return self
     */
    public function setUserName(?string $userName): self;

    /**
     * @return string|null
     */
    public function getUserName(): ?string;

    /**
     * @param string|null $createdAt
     * @return self
     */
    public function setCreatedAt(?string $createdAt): self;

    /**
     * @return string|null
     */
    public function getCreatedAt(): ?string;

    /**
     * @param bool $flag
     * @return self
     */
    public function setIsActive(bool $flag): self;

    /**
     * @return bool
     */
    public function getIsActive(): bool;

    /**
     * @param string|null $updatedAt
     * @return self
     */
    public function setTelegramUpdatedAt(?string $updatedAt): self;

    /**
     * @return string|null
     */
    public function getTelegramUpdatedAt(): ?string;
}
