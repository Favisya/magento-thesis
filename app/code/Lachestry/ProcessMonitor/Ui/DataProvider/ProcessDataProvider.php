<?php

declare(strict_types=1);

namespace Lachestry\ProcessMonitor\Ui\DataProvider;

use Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider;
use Magento\Framework\View\Element\UiComponent\DataProvider\CollectionFactory;
use Magento\Framework\Api\Search\ReportingInterface;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Api\FilterBuilder;
use Lachestry\ProcessMonitor\Model\Process;

class ProcessDataProvider extends DataProvider
{
    /**
     * @var Process
     */
    private $processModel;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        ReportingInterface $reporting,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        RequestInterface $request,
        FilterBuilder $filterBuilder,
        Process $processModel,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $reporting,
            $searchCriteriaBuilder,
            $request,
            $filterBuilder,
            $meta,
            $data
        );
        $this->processModel = $processModel;
    }

    public function getData()
    {
        $processes = $this->processModel->getProcessList();
        $items = [];

        foreach ($processes as $process) {
            $items[] = [
                'pid'           => $process['pid'],
                'user'          => $process['user'],
                'cpu'           => $process['cpu'],
                'memory'        => $process['memory'],
                'execution_time' => $process['execution_time'],
                'command'       => $process['command'],
            ];
        }

        return [
            'totalRecords' => count($items),
            'items'        => $items,
        ];
    }
}
