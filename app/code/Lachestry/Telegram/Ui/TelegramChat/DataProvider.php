<?php
declare(strict_types=1);

namespace Lachestry\Telegram\Ui\TelegramChat;

use Magento\Framework\App\Request\Http;
use Lachestry\Telegram\Model\ResourceModel\TelegramChat\Grid\Collection;
use Lachestry\Telegram\Model\ResourceModel\TelegramChat\Grid\CollectionFactory;
use Magento\Ui\DataProvider\AbstractDataProvider;

class DataProvider extends AbstractDataProvider
{
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        Http $httpHandler,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $meta,
            $data
        );
        $this->collection = $collectionFactory->create();
    }

    public function getData(): array
    {
        return $this->getCollection()->toArray();
    }

    public function getCollection(): Collection
    {
        return $this->collection;
    }
}
