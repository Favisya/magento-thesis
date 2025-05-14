<?php
declare(strict_types=1);

namespace Lachestry\LogMonitor\Model;

use Lachestry\Configuration\Model\Config;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\App\Filesystem\DirectoryList;

class LogMonitor
{
    private Config $config;
    private File $file;
    private DirectoryList $directoryList;

    public function __construct(
        Config $config,
        File $file,
        DirectoryList $directoryList
    ) {
        $this->config = $config;
        $this->file = $file;
        $this->directoryList = $directoryList;
    }

    public function checkLogForErrors(): array
    {
        $logPath = $this->getAbsoluteLogPath();
        
        if (!$logPath || !$this->file->fileExists($logPath)) {
            return [];
        }
        
        $content = $this->file->fileGetContents($logPath);
        
        if (empty($content)) {
            return [];
        }
        
        $lines = explode("\n", $content);
        $errors = [];
        
        foreach ($lines as $line) {
            if (empty($line)) {
                continue;
            }
            
            // Проверяем, содержит ли строка ошибку
            if (stripos($line, 'error') !== false || stripos($line, 'exception') !== false || stripos($line, 'critical') !== false) {
                // Пытаемся извлечь временную метку в начале строки
                if (preg_match('/^(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})/', $line, $matches)) {
                    $timestamp = $matches[1];
                } else {
                    $timestamp = date('Y-m-d H:i:s');
                }
                
                $errors[] = [
                    'timestamp' => $timestamp,
                    'message' => $line
                ];
            }
        }
        
        return $errors;
    }

    public function clearLog(): void
    {
        $logPath = $this->getAbsoluteLogPath();
        
        if ($logPath && $this->file->fileExists($logPath)) {
            $this->file->filePutContents($logPath, '');
        }
    }
    
    private function getAbsoluteLogPath(): ?string
    {
        $logPath = $this->config->getConfigValue('lachestry/log_monitor/log_path');
        
        if (empty($logPath)) {
            return null;
        }
        
        return $this->directoryList->getRoot() . DIRECTORY_SEPARATOR . $logPath;
    }
} 