<?php
declare(strict_types=1);

namespace Lachestry\Telegram\Controller\Adminhtml\Chat;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Ui\Component\MassAction\Filter;
use Lachestry\Telegram\Model\ResourceModel\TelegramChat\CollectionFactory;
use Lachestry\Telegram\Model\ResourceModel\TelegramChat;

class MassDelete extends Action
{
    const ADMIN_RESOURCE = 'Lachestry_Telegram::telegram_chats';

    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var TelegramChat
     */
    protected $telegramChatResource;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param TelegramChat $telegramChatResource
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        TelegramChat $telegramChatResource
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->telegramChatResource = $telegramChatResource;
        parent::__construct($context);
    }

    /**
     * Execute action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        try {
            $collection = $this->filter->getCollection($this->collectionFactory->create());
            $deleted = 0;
            foreach ($collection as $item) {
                $item->delete();
                $deleted++;
            }

            $this->messageManager->addSuccessMessage(
                __('Удалено чатов: %1', $deleted)
            );
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage(
                $e,
                __('Произошла ошибка при удалении чатов')
            );
        }

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }
} 