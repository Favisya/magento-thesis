<?php
declare(strict_types=1);

namespace Lachestry\LogMonitor\Api;

interface LogErrorInterface
{
    const ENTITY_ID = 'entity_id';
    const LOG_FILE = 'log_file';
    const DATE = 'date';
    const SEVERITY = 'severity';
    const MESSAGE = 'message';
    const CONTEXT = 'context';

    public function getEntityId(): ?int;
    
    public function setEntityId(int $entityId): self;
    
    public function getLogFile(): string;
    
    public function setLogFile(string $logFile): self;
    
    public function getDate(): string;
    
    public function setDate(string $date): self;
    
    public function getSeverity(): string;
    
    public function setSeverity(string $severity): self;
    
    public function getMessage(): string;
    
    public function setMessage(string $message): self;
    
    public function getContext(): ?string;
    
    public function setContext(?string $context): self;
} 