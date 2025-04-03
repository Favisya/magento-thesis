<?php

declare(strict_types=1);

namespace Lachestry\LogMonitor\Model;

use Lachestry\LogMonitor\Api\LogErrorInterface;
use Lachestry\LogMonitor\Api\LogErrorRepositoryInterface;
use Lachestry\LogMonitor\Model\ResourceModel\LogError as LogErrorResource;
use Lachestry\LogMonitor\Model\ResourceModel\LogError\CollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Api\SearchResultsInterfaceFactory;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class LogErrorRepository implements LogErrorRepositoryInterface
{
    private LogErrorFactory $logErrorFactory;
    private LogErrorResource $logErrorResource;
    private CollectionFactory $collectionFactory;
    private SearchResultsInterfaceFactory $searchResultsFactory;
    private CollectionProcessorInterface $collectionProcessor;

    public function __construct(
        LogErrorFactory $logErrorFactory,
        LogErrorResource $logErrorResource,
        CollectionFactory $collectionFactory,
        SearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->logErrorFactory = $logErrorFactory;
        $this->logErrorResource = $logErrorResource;
        $this->collectionFactory = $collectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $collectionProcessor;
    }

    public function save(LogErrorInterface $logError): LogErrorInterface
    {
        try {
            $this->logErrorResource->save($logError);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__($e->getMessage()));
        }
        return $logError;
    }

    public function getById(int $entityId): LogErrorInterface
    {
        $logError = $this->logErrorFactory->create();
        $this->logErrorResource->load($logError, $entityId);
        if (!$logError->getId()) {
            throw new NoSuchEntityException(__('The log error with ID "%1" doesn\'t exist.', $entityId));
        }
        return $logError;
    }

    public function getList(SearchCriteriaInterface $searchCriteria): SearchResultsInterface
    {
        $collection = $this->collectionFactory->create();
        
        $this->collectionProcessor->process($searchCriteria, $collection);
        
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    public function delete(LogErrorInterface $logError): bool
    {
        try {
            $this->logErrorResource->delete($logError);
        } catch (\Exception $e) {
            throw new CouldNotDeleteException(__($e->getMessage()));
        }
        return true;
    }

    public function deleteById(int $entityId): bool
    {
        return $this->delete($this->getById($entityId));
    }
}
