<?php
declare(strict_types=1);

namespace Lachestry\Cron\Setup\Patch\Data;

use Lachestry\Cron\Helper\Data;
use Lachestry\Cron\Api\Data\JobCodeInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Lachestry\Cron\Model\ResourceModel\JobCodes;
use Lachestry\Cron\Model\ResourceModel\JobCodes\Collection;

class InstallCurrentJobCodes implements DataPatchInterface
{
    protected ModuleDataSetupInterface $moduleDataSetup;
    protected JobCodes $JobCodesResource;
    protected Collection $collection;

    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        Collection $collection,
        JobCodes $JobCodesResource
    ) {
        $this->collection = $collection;
        $this->JobCodesResource = $JobCodesResource;
        $this->moduleDataSetup = $moduleDataSetup;
    }

    public static function getDependencies()
    {
        return [];
    }

    public function getAliases()
    {
        return [];
    }

    public function apply()
    {
        $this->moduleDataSetup->startSetup();

        $this->collection->synchronizeJobCodes();

        $this->moduleDataSetup->endSetup();
    }
}
