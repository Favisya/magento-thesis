<?php
declare(strict_types=1);

namespace Lachestry\LogMonitor\Ui\DataProvider;

use Lachestry\LogMonitor\Model\ResourceModel\LogError\Grid\CollectionFactory;
use Magento\Framework\Api\Filter;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\ReportingInterface;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider;

class LogErrorDataProvider extends DataProvider
{
    protected CollectionFactory $collectionFactory;
    protected FilterBuilder $filterBuilder;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        ReportingInterface $reporting,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        RequestInterface $request,
        FilterBuilder $filterBuilder,
        CollectionFactory $collectionFactory,
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
            $meta,
            $data
        );
        $this->filterBuilder = $filterBuilder;
        $this->collectionFactory = $collectionFactory;
    }

    protected function searchResultToOutput($searchResult)
    {
        $arrItems = [];
        $arrItems['items'] = [];
        
        foreach ($searchResult->getItems() as $item) {
            $itemData = $item->getData();
            $arrItems['items'][] = $itemData;
        }
        
        $arrItems['totalRecords'] = $searchResult->getTotalCount();
        
        return $arrItems;
    }
    
    public function addFilter(Filter $filter)
    {
        $this->filterBuilder->setField($filter->getField());
        $this->filterBuilder->setValue($filter->getValue());
        $this->filterBuilder->setConditionType($filter->getConditionType());
        
        $this->searchCriteriaBuilder->addFilter($this->filterBuilder->create());
    }
} 