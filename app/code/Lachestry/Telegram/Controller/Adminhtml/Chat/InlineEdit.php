<?php
declare(strict_types=1);

namespace Lachestry\Telegram\Controller\Adminhtml\Chat;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\Result\JsonFactory;
use Lachestry\Telegram\Model\TelegramChatFactory;

class InlineEdit extends Action
{
    const ADMIN_RESOURCE = 'Lachestry_Telegram::telegram_chats';

    protected JsonFactory $jsonFactory;
    protected TelegramChatFactory $telegramChatFactory;

    public function __construct(
        Action\Context $context,
        JsonFactory $jsonFactory,
        TelegramChatFactory $telegramChatFactory
    ) {
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->telegramChatFactory = $telegramChatFactory;
    }

    /**
     * Execute action based on request and return result as JSON
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $resultJson = $this->jsonFactory->create();
        $error = false;
        $messages = [];

        $postItems = $this->getRequest()->getParam('items', []);

        if (empty($postItems)) {
            // Просто ничего не делать и вернуть success
            return $resultJson->setData(['messages' => [], 'error' => false]);
        }

        if (!$this->getRequest()->isAjax()) {
            $messages[] = __('Please correct the data sent.');
            return $resultJson->setData(['messages' => $messages, 'error' => true]);
        }

        foreach (array_keys($postItems) as $modelId) {
            $model = $this->telegramChatFactory->create()->load($modelId);

            if (!$model->getId()) {
                $messages[] = __('Telegram Chat with ID %1 no longer exists.', $modelId);
                $error = true;
                continue;
            }
            try {
                $model->addData($postItems[$modelId]);
                $model->save();
            } catch (\Exception $e) {
                $messages[] = __('Error while saving Telegram Chat with ID %1: %2', $modelId, $e->getMessage());
                $error = true;
            }
        }
        return $resultJson->setData(['messages' => $messages, 'error' => $error]);
    }
}
