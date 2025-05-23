<?php

declare(strict_types=1);

namespace Lachestry\Telegram\Controller\Adminhtml\Chat;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Ui\Component\MassAction\Filter;
use Lachestry\Telegram\Model\ResourceModel\TelegramChat\CollectionFactory;
use Lachestry\Telegram\Api\Data\TelegramChatInterface;

class MassEnable extends Action
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
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
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
            $enabled = 0;
            foreach ($collection as $item) {
                $item->setData(TelegramChatInterface::IS_ACTIVE, 1);
                $item->save();
                $enabled++;
            }

            $this->messageManager->addSuccessMessage(
                __('Включено чатов: %1', $enabled)
            );
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage(
                $e,
                __('Произошла ошибка при включении чатов')
            );
        }

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }
}
