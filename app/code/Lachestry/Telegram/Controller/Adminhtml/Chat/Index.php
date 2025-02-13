<?php
declare(strict_types=1);

namespace Lachestry\Telegram\Controller\Adminhtml\Chat;

use Magento\Backend\App\Action;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\App\Action\Context;

class Index extends Action
{
    const ADMIN_RESOURCE = 'Lachestry_Telegram::telegram_chats';

    protected PageFactory $resultPageFactory;

    /**
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Execute action
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Lachestry_Telegram::telegram_chats');
        $resultPage->getConfig()->getTitle()->prepend(__('Telegram Chats'));
        return $resultPage;
    }
}
