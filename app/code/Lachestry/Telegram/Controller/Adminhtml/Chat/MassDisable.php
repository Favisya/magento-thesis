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

class MassDisable extends Action
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
            $disabled = 0;
            foreach ($collection as $item) {
                $item->setData(TelegramChatInterface::IS_ACTIVE, 0);
                $item->save();
                $disabled++;
            }

            $this->messageManager->addSuccessMessage(
                __('Отключено чатов: %1', $disabled)
            );
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage(
                $e,
                __('Произошла ошибка при отключении чатов')
            );
        }

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }
} 