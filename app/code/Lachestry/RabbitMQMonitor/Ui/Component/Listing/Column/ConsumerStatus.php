<?php

declare(strict_types=1);

namespace Lachestry\RabbitMQMonitor\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class ConsumerStatus extends Column
{
    private const STATUS_CLASSES = [
        'Running'  => 'grid-severity-notice',
        'Disabled' => 'grid-severity-minor',
        'Stopped'  => 'grid-severity-critical',
    ];

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface   $context,
        UiComponentFactory $uiComponentFactory,
        array              $components = [],
        array              $data = [],
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource): array
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                if (isset($item[$this->getData('name')])) {
                    $status   = $item[$this->getData('name')];
                    $cssClass = self::STATUS_CLASSES[$status] ?? '';

                    if ($cssClass) {
                        $item[$this->getData('name')] = '<span class="' . $cssClass . '">' .
                            '<span>' . $status . '</span></span>';
                    }
                }
            }
        }

        return $dataSource;
    }
}
