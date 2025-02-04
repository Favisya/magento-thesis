<?php
declare(strict_types=1);

namespace Lachestry\CronMonitoring\Ui\Schedule\Grid;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Lachestry\CronMonitoring\Block\Adminhtml\Grid\Schedule\Message as MessageBlock;

class Message extends Column
{
    protected const FIELD_NAME = 'messages';

    protected MessageBlock $messageBlock;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        MessageBlock $messageBlock,
        array $components = [],
        array $data = [])
    {
        $this->messageBlock = $messageBlock;
        parent::__construct(
            $context,
            $uiComponentFactory,
            $components,
            $data
        );
    }

    public function prepareDataSource(array $dataSource)
    {
        foreach ($dataSource['data']['items'] as &$item) {
            $item[static::FIELD_NAME] = $this->messageBlock->renderMessage($item[static::FIELD_NAME]);
        }

        return $dataSource;
    }
}
