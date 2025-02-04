<?php
declare(strict_types=1);

namespace Lachestry\Cron\Model\ResourceModel\JobCodes;

use Lachestry\Cron\Api\Data\JobCodeInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot;
use Magento\Sales\Model\ResourceModel\Collection\AbstractCollection;
use Lachestry\Cron\Model\JobCode as JobCodesModel;
use Lachestry\Cron\Model\ResourceModel\JobCodes as JobCodesResource;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\Api\DataObjectHelper;
use Psr\Log\LoggerInterface;

class Collection extends AbstractCollection
{
    protected DataObjectHelper $dataObjectHelper;

    public function __construct(
        EntityFactoryInterface $entityFactory,
        LoggerInterface        $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface       $eventManager,
        Snapshot               $entitySnapshot,
        DataObjectHelper       $dataObjectHelper,
        AdapterInterface       $connection = null,
        AbstractDb             $resource = null
    ) {
        $this->dataObjectHelper = $dataObjectHelper;

        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $entitySnapshot,
            $connection,
            $resource
        );
    }

    protected function _construct()
    {
        $this->_init(JobCodesModel::class, JobCodesResource::class);
    }

    protected function _beforeLoad()
    {
        if ($this->isLoaded()) {
            $this->synchronizeJobCodes();
        }

        return parent::_beforeLoad();
    }

    public function synchronizeJobCodes(): self
    {
        /** @var JobCodesResource $resourceModel */
        $resourceModel = $this->getResource();
        if ($resourceModel->isSynchronized()) {
            return $this;
        }

        $mageJobCodes = $resourceModel->getAllFromConfig();
        $jobCodesInDB = $resourceModel->parseJobCodes($this->getData());

        $jobCodesNamesToSync = $resourceModel->getNamesToSync($jobCodesInDB, $mageJobCodes);
        $jobCodesToSync = array_intersect_key($mageJobCodes, $jobCodesNamesToSync);

        $updatedJobCodes = $this->prepareJobCodes($jobCodesToSync);

        foreach ($updatedJobCodes as $jobCode) {
            if ($id = $jobCode->getId()) {
                $this->removeItemByKey($id);
            }

            $this->addItem($jobCode);
        }

        if ($updatedJobCodes) {
            $this->save();
        }

        return $this;
    }

    public function prepareJobCodes(array $mageJobCodes): array
    {
        $jobCodeNames = array_keys($mageJobCodes);
        $exisitngIds = $this->getExistingIdsByNames($jobCodeNames);

        $jobCodes = [];
        foreach ($mageJobCodes as $name => $jobCode) {
            /** @var JobCodesModel $jobCodeModel */
            $jobCodeModel = $this->getNewEmptyItem();

            $this->dataObjectHelper->populateWithArray(
                $jobCodeModel,
                $jobCode,
                JobCodeInterface::class
            );

            if (!empty($exisitngIds[$name])) {
                $jobCodeModel->setId($exisitngIds[$name]);
            }


            $jobCodes[] = $jobCodeModel;
        }

        return $jobCodes;
    }

    protected function getExistingIdsByNames(array $names): array
    {
        $items = $this->addFieldToFilter(
            JobCodeInterface::JOB_CODE,
            ['in' => $names]
        )->getItems();

        $result = [];

        /** @var JobCodesModel $jobCode */
        foreach ($items as $jobCode) {
            $result[$jobCode->getJobCodeName()] = (int) $jobCode->getId();
        }

        return $result;
    }
}
