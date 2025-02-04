<?php
declare(strict_types=1);

namespace Lachestry\Cron\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Lachestry\Cron\Api\Data\JobCodeInterface as JobCode;
use Magento\Cron\Model\Config\Data;
use Lachestry\Configuration\Model\ModuleHandler;

class JobCodes extends AbstractDb
{
    const TABLE = 'lachestry_job_codes_info';

    protected Data $mageJobCodes;
    protected ModuleHandler $moduleHandler;

    public function __construct(
        Context $context,
        Data $jobCodes,
        ModuleHandler $moduleHandler,
        $connectionName = null
    ) {
        $this->moduleHandler = $moduleHandler;
        $this->mageJobCodes = $jobCodes;
        parent::__construct($context, $connectionName);
    }

    public function _construct()
    {
        $this->_init(self::TABLE, 'id');
    }

    public function getAllFromDB(): array
    {
        $connection = $this->getConnection();
        $query = $connection->select()->from($this->getMainTable());

        $jobCodes = $connection->fetchAssoc($query);

        return $this->parseJobCodes($jobCodes);
    }

    public function getAllFromConfig(): array
    {
        $allCodes = [];
        foreach ($this->mageJobCodes->getJobs() as $group => $jobCodesByGroup) {
            foreach ($jobCodesByGroup as $jobCode) {
                $jobCode[JobCode::GROUP] = $group;
                $allCodes[] = $jobCode;
            }
        }

        return $this->parseJobCodes($allCodes);
    }

    public function isSynchronized(): bool
    {
        $allInDB     = $this->getAllFromDB();
        $allExisting = $this->getAllFromConfig();

        if (count($allInDB) != count($allExisting)) {
            return false;
        }

        return empty($this->getNamesToSync($allInDB, $allExisting));
    }

    public function getNamesToSync(array $codesInDB, array $allCodes)
    {
        $result = [];
        foreach ($allCodes as $name => $jobCode) {
            $jobCodeInDB = $codesInDB[$name] ?? [];
            $difference = array_diff_assoc($jobCode, $jobCodeInDB);

            if ($difference) {
                $result[$name] = $name;
            }
        }

        return $result;
    }

    public function parseJobCodes(array $jobCodes): array
    {
        $result = [];
        foreach ($jobCodes as $jobCode) {
            $name = $jobCode['name'] ?? $jobCode[JobCode::JOB_CODE];
            $module = $jobCode[JobCode::MODULE] ?? $this->moduleHandler->getModuleName($jobCode['instance']);

            $result[$name] = [
                JobCode::JOB_CODE    => $name,
                JobCode::MODULE      => $module,
                JobCode::GROUP       => $jobCode[JobCode::GROUP],
                JobCode::CONFIG_PATH => $jobCode[JobCode::CONFIG_PATH] ?? null,
                JobCode::SCHEDULE    => $jobCode[JobCode::SCHEDULE] ?? null
            ];
        }

        return $result;
    }
}
