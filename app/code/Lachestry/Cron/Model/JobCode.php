<?php
declare(strict_types=1);

namespace Lachestry\Cron\Model;

use Lachestry\Cron\Api\Data\JobCodeInterface;
use Lachestry\Configuration\Model\ModuleHandler;
use Magento\Framework\Model\AbstractModel;
use Lachestry\Cron\Model\ResourceModel\JobCodes as JobCodesResource;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Registry;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;

class JobCode extends AbstractModel implements JobCodeInterface
{
    protected ModuleHandler $moduleHandler;

    public function __construct(
        Context $context,
        Registry $registry,
        ModuleHandler $dataHelper,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->moduleHandler = $dataHelper;
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
    }

    protected function _construct()
    {
        $this->_init(JobCodesResource::class);
        parent::_construct();
    }

    public function setJobCodeName(string $jobCode): self
    {
        $this->setData(self::JOB_CODE, $jobCode);
        return $this;
    }

    public function getJobCodeName(): string
    {
        return $this->getData(self::JOB_CODE);
    }

    public function setSchedule(?string $schedule): self
    {
        $this->setData(self::SCHEDULE, $schedule);
        return $this;
    }

    public function getSchedule(): ?string
    {
        return $this->getData(self::SCHEDULE);
    }

    public function setModule(string $module): self
    {
        $module = $this->moduleHandler->getModuleName($module);
        $this->setData(self::MODULE, $module);
        return $this;
    }

    public function getModule(): string
    {
        return $this->getData(self::MODULE);
    }

    public function setConfigPath(?string $path): self
    {
        $this->setData(self::CONFIG_PATH, $path);
        return $this;
    }

    public function getConfigPath(): ?string
    {
        return $this->getData(self::CONFIG_PATH);
    }

    public function setGroup(string $group): self
    {
        $this->setData(self::GROUP, $group);
        return $this;
    }

    public function getGroup(): string
    {
        return $this->getData(self::GROUP);
    }
}
