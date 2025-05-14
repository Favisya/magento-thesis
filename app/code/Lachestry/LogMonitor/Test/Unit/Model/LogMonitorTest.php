<?php
declare(strict_types=1);

namespace Lachestry\LogMonitor\Test\Unit\Model;

use PHPUnit\Framework\TestCase;
use Lachestry\LogMonitor\Model\LogMonitor;
use Lachestry\Configuration\Model\Config;
use Magento\Framework\App\Filesystem\DirectoryList;
use PHPUnit\Framework\MockObject\MockObject;

class LogMonitorTest extends TestCase
{
    protected Config|MockObject $config;
    protected DirectoryList|MockObject $directoryList;
    protected FileMock $fileMock;

    public function testCheckLogForErrors(): void
    {
        $this->createMocks();
        
        $this->config->expects($this->once())
            ->method('getConfigValue')
            ->willReturn('var/log/system.log');
            
        $this->directoryList->expects($this->once())
            ->method('getRoot')
            ->willReturn('/var/www/html/magento');
            
        $this->fileMock->setFileExists(true);
        $this->fileMock->setFileContent(
            "2023-12-01 10:00:00 ERROR: Critical error in module X\n" .
            "2023-12-01 11:00:00 INFO: Process completed\n"
        );
        
        $logMonitor = new LogMonitor(
            $this->config, 
            $this->fileMock, 
            $this->directoryList
        );
        
        $result = $logMonitor->checkLogForErrors();
        
        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertStringContainsString('ERROR: Critical error', $result[0]['message']);
    }
    
    public function testCheckLogForErrorsEmptyLog(): void
    {
        $this->createMocks();
        
        $this->config->expects($this->once())
            ->method('getConfigValue')
            ->willReturn('var/log/system.log');
            
        $this->directoryList->expects($this->once())
            ->method('getRoot')
            ->willReturn('/var/www/html/magento');
            
        $this->fileMock->setFileExists(true);
        $this->fileMock->setFileContent('');
        
        $logMonitor = new LogMonitor(
            $this->config, 
            $this->fileMock, 
            $this->directoryList
        );
        
        $result = $logMonitor->checkLogForErrors();
        
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }
    
    public function testCheckLogForErrorsNonExistentLog(): void
    {
        $this->createMocks();
        
        $this->config->expects($this->once())
            ->method('getConfigValue')
            ->willReturn('var/log/system.log');
            
        $this->directoryList->expects($this->once())
            ->method('getRoot')
            ->willReturn('/var/www/html/magento');
            
        $this->fileMock->setFileExists(false);
        
        $logMonitor = new LogMonitor(
            $this->config, 
            $this->fileMock, 
            $this->directoryList
        );
        
        $result = $logMonitor->checkLogForErrors();
        
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testClearLog(): void
    {
        $this->createMocks();
        
        $this->config->expects($this->once())
            ->method('getConfigValue')
            ->willReturn('var/log/system.log');
            
        $this->directoryList->expects($this->once())
            ->method('getRoot')
            ->willReturn('/var/www/html/magento');
            
        $this->fileMock->setFileExists(true);
        $this->fileMock->setFileContent('Old content');
        
        $logMonitor = new LogMonitor(
            $this->config, 
            $this->fileMock, 
            $this->directoryList
        );
        
        $logMonitor->clearLog();
        
        $this->assertEquals('', $this->fileMock->fileGetContents(''));
    }
    
    public function testClearLogNonExistentFile(): void
    {
        $this->createMocks();
        
        $this->config->expects($this->once())
            ->method('getConfigValue')
            ->willReturn('var/log/system.log');
            
        $this->directoryList->expects($this->once())
            ->method('getRoot')
            ->willReturn('/var/www/html/magento');
            
        $this->fileMock->setFileExists(false);
        $this->fileMock->setFileContent('Old content');
        
        $logMonitor = new LogMonitor(
            $this->config, 
            $this->fileMock, 
            $this->directoryList
        );
        
        $logMonitor->clearLog();
        
        $this->assertEquals('Old content', $this->fileMock->fileGetContents(''));
    }
    
    protected function createMocks(): void
    {
        $this->config = $this->createMock(Config::class);
        $this->directoryList = $this->createMock(DirectoryList::class);
        $this->fileMock = new FileMock();
    }
} 