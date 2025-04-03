<?php

declare(strict_types=1);

namespace Lachestry\LogMonitor\Model;

use Lachestry\LogMonitor\Api\LogErrorInterface;
use Magento\Framework\Model\AbstractModel;
use Lachestry\LogMonitor\Model\ResourceModel\LogError as LogErrorResource;

class LogError extends AbstractModel implements LogErrorInterface
{
    protected function _construct()
    {
        $this->_init(LogErrorResource::class);
    }

    public function getEntityId(): ?int
    {
        return $this->getData(self::ENTITY_ID) ? (int)$this->getData(self::ENTITY_ID) : null;
    }

    public function setEntityId($entityId): self
    {
        return $this->setData(self::ENTITY_ID, $entityId);
    }

    public function getLogFile(): string
    {
        return (string)$this->getData(self::LOG_FILE);
    }

    public function setLogFile(string $logFile): self
    {
        return $this->setData(self::LOG_FILE, $logFile);
    }

    public function getDate(): string
    {
        return (string)$this->getData(self::DATE);
    }

    public function setDate(string $date): self
    {
        return $this->setData(self::DATE, $date);
    }

    public function getSeverity(): string
    {
        return (string)$this->getData(self::SEVERITY);
    }

    public function setSeverity(string $severity): self
    {
        return $this->setData(self::SEVERITY, $severity);
    }

    public function getMessage(): string
    {
        return (string)$this->getData(self::MESSAGE);
    }

    public function setMessage(string $message): self
    {
        return $this->setData(self::MESSAGE, $message);
    }

    public function getContext(): ?string
    {
        return $this->getData(self::CONTEXT);
    }

    public function setContext(?string $context): self
    {
        return $this->setData(self::CONTEXT, $context);
    }
}
