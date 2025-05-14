<?php
declare(strict_types=1);

namespace Lachestry\LogMonitor\Test\Unit\Model;

use Magento\Framework\Filesystem\Driver\File;

/**
 * Мок-класс для File, чтобы избежать проблем со сложным мокингом
 */
class FileMock extends File
{
    protected bool $fileExists = true;
    protected string $fileContent = '';
    
    public function __construct()
    {
        // Пустой конструктор, чтобы избежать вызова родительского конструктора
    }
    
    public function setFileExists(bool $exists): void
    {
        $this->fileExists = $exists;
    }
    
    public function setFileContent(string $content): void
    {
        $this->fileContent = $content;
    }
    
    public function fileExists($path): bool
    {
        return $this->fileExists;
    }
    
    public function fileGetContents($path, $flag = null, $context = null): string
    {
        return $this->fileContent;
    }
    
    public function filePutContents($path, $content, $mode = null, $context = null): int
    {
        $this->fileContent = $content;
        return strlen($content);
    }
} 