<?php
declare(strict_types=1);

namespace Lachestry\LogMonitor\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

interface LogErrorRepositoryInterface
{
    public function save(LogErrorInterface $logError): LogErrorInterface;
    
    public function getById(int $entityId): LogErrorInterface;
    
    public function getList(SearchCriteriaInterface $searchCriteria): SearchResultsInterface;
    
    public function delete(LogErrorInterface $logError): bool;
    
    public function deleteById(int $entityId): bool;
} 