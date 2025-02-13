<?php
declare(strict_types=1);

namespace Lachestry\Telegram\Cron;

use Lachestry\Telegram\Model\Config;
use Psr\Log\LoggerInterface;
use Lachestry\Telegram\Api\NotificationInterface;
use Lachestry\Telegram\Model\ResourceModel\TelegramChat as TgChatResource;
use Lachestry\Telegram\Api\Data\TelegramChatInterface as TgChInterface;

class BotUpdatesCollect
{
    protected const MY_MEMBER  = 'my_chat_member';
    protected const NEW_MEMBER = 'new_chat_member';

    public function __construct(
        protected readonly Config $config,
        protected readonly LoggerInterface $logger,
        protected readonly TgChatResource $telegramChatResource,
        protected readonly NotificationInterface $notificationProvider,
    ) {}

    public function execute(): void
    {
        if (!$this->config->isEnabled()) {
            return;
        }

        $response = $this->notificationProvider->getMessages();

        $telegramChatsToAdd    = [];
        $telegramChatsToDelete = [];

        foreach ($response as $updateRecord) {
            if (!isset($updateRecord[self::MY_MEMBER])) {
                continue;
            }

            $unixUpdateDate      = $updateRecord[self::MY_MEMBER]['date'] ?? null;
            $formattedUpdateDate = Date('Y-m-d H:i:s', $unixUpdateDate);

            $record = [];
            $record[TgChInterface::TELEGRAM_UPDATED_AT] = $formattedUpdateDate;
            $record[TgChInterface::CHAT_NAME]           = $updateRecord[self::MY_MEMBER]['chat']['title'] ?? null;
            $record[TgChInterface::USER_NAME]           = $updateRecord[self::MY_MEMBER]['from']['username'] ?? null;
            $record[TgChInterface::CHAT_ID]             = $updateRecord[self::MY_MEMBER]['chat']['id'] ?? null;
            $record[TgChInterface::IS_ACTIVE]           = true;

            $status = $updateRecord[self::MY_MEMBER][self::NEW_MEMBER]['status'];
            if ($status == 'member') {
                $telegramChatsToAdd[$record[TgChInterface::CHAT_ID]] = $record;
                continue;
            }

            if ($status == 'left') {
                $telegramChatsToDelete[$record[TgChInterface::CHAT_ID]] = $record;
            }
        }

        $this->compareChatDates($telegramChatsToDelete, $telegramChatsToAdd);

        try {
            $this->telegramChatResource->saveChats($telegramChatsToAdd);
            $this->telegramChatResource->deleteChats($telegramChatsToDelete);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }


    protected function compareChatDates(array &$telegramChatsToDelete, array &$telegramChatsToAdd): void
    {
        $samesChatsIds = array_intersect(array_keys($telegramChatsToDelete), array_keys($telegramChatsToAdd));

        foreach ($samesChatsIds as $chatId) {
            $deleteRecord = $telegramChatsToDelete[$chatId];
            $addRecord    = $telegramChatsToAdd[$chatId];

            $addRecordUpdateDate    = $addRecord[TgChInterface::TELEGRAM_UPDATED_AT];
            $deleteRecordUpdateDate = $deleteRecord[TgChInterface::TELEGRAM_UPDATED_AT];

            if ($deleteRecordUpdateDate > $addRecordUpdateDate) {
                unset($telegramChatsToAdd[$chatId]);
                continue;
            }

            unset($telegramChatsToDelete[$chatId]);
        }
    }
}
