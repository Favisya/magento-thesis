<?php

namespace Lachestry\Cron\Api\Data;

interface JobCodeInterface
{
    public const ID          = 'id';
    public const JOB_CODE    = 'job_code_name';
    public const SCHEDULE    = 'schedule';
    public const MODULE      = 'module';
    public const CONFIG_PATH = 'config_path';
    public const GROUP       = 'group';

    /**
     * @param string $jobCode
     * @return self
     */
    public function setJobCodeName(string $jobCode): self;

    /**
     * @return string
     */
    public function getJobCodeName(): string;

    /**
     * @param null|string $schedule
     * @return self
     */
    public function setSchedule(?string $schedule): self;
    /**
     * @return string|null
     */
    public function getSchedule(): ?string;

    /**
     * @param string $module
     * @return self
     */
    public function setModule(string $module): self;

    /**
     * @return string
     */
    public function getModule(): string;

    /**
     * @param string|null $path
     * @return self
     */
    public function setConfigPath(?string $path): self;

    /**
     * @return string|null
     */
    public function getConfigPath(): ?string;

    /**
     * @param string $group
     * @return self
     */
    public function setGroup(string $group): self;

    /**
     * @return string
     */
    public function getGroup(): string;
}
