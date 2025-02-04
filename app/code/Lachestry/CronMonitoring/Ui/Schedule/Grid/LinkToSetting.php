<?php
declare(strict_types=1);

namespace Lachestry\CronMonitoring\Ui\Schedule\Grid;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Lachestry\Cron\Api\Data\JobCodeInterface;
use Lachestry\Configuration\Block\Link\LinkToSeting;

class LinkToSetting extends Column
{
    protected LinkToSeting $linkToSeting;

    public function __construct(
        ContextInterface   $context,
        UiComponentFactory $uiComponentFactory,
        LinkToSeting       $linkToSeting,
        array              $components = [],
        array              $data = []
    ) {
        $this->linkToSeting = $linkToSeting;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        foreach ($dataSource['data']['items'] as &$item) {
            $configPath = $item[JobCodeInterface::CONFIG_PATH] ?? null;
            $item['view'] = $this->linkToSeting->buildLink($configPath);
        }

        return $dataSource;
    }
}
