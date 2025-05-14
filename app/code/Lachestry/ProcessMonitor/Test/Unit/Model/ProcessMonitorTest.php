<?php
declare(strict_types=1);

/**
 * Тест для монитора процессов
 */
namespace Lachestry\ProcessMonitor\Test\Unit\Model;

use PHPUnit\Framework\TestCase;
use Lachestry\ProcessMonitor\Model\ProcessMonitor;
use Lachestry\Configuration\Model\Config;
use Magento\Framework\Shell;
use Magento\Framework\Shell\CommandRendererInterface;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @covers \Lachestry\ProcessMonitor\Model\ProcessMonitor
 */
class ProcessMonitorTest extends TestCase
{
    /**
     * @var ProcessMonitor
     */
    protected ProcessMonitor $processMonitor;

    /**
     * @var Config|\PHPUnit\Framework\MockObject\MockObject
     */
    protected Config|MockObject $config;

    /**
     * @var Shell|\PHPUnit\Framework\MockObject\MockObject
     */
    protected Shell|MockObject $shell;

    /**
     * @var CommandRendererInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected CommandRendererInterface|MockObject $commandRenderer;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->config = $this->createMock(Config::class);
        $this->shell = $this->createMock(Shell::class);
        $this->commandRenderer = $this->createMock(CommandRendererInterface::class);
        
        $this->processMonitor = new ProcessMonitor(
            $this->config,
            $this->shell,
            $this->commandRenderer
        );
    }

    /**
     * Тест проверки процесса
     */
    public function testCheckProcessWhenProcessExists(): void
    {
        $processName = 'php-fpm';
        $expectedCommand = 'pgrep -f php-fpm';
        $expectedOutput = "1234\n5678";

        $this->commandRenderer->expects($this->once())
            ->method('render')
            ->with('pgrep', ['-f', $processName])
            ->willReturn($expectedCommand);

        $this->shell->expects($this->once())
            ->method('execute')
            ->with($expectedCommand)
            ->willReturn($expectedOutput);

        $result = $this->processMonitor->checkProcess($processName);
        $this->assertTrue($result);
    }

    public function testCheckProcessWhenProcessDoesNotExist(): void
    {
        $processName = 'non-existent-process';
        $expectedCommand = 'pgrep -f non-existent-process';

        $this->commandRenderer->expects($this->once())
            ->method('render')
            ->with('pgrep', ['-f', $processName])
            ->willReturn($expectedCommand);

        $this->shell->expects($this->once())
            ->method('execute')
            ->with($expectedCommand)
            ->willReturn('');

        $result = $this->processMonitor->checkProcess($processName);
        $this->assertFalse($result);
    }

    public function testCheckProcessWhenCommandFails(): void
    {
        $processName = 'php-fpm';
        $expectedCommand = 'pgrep -f php-fpm';

        $this->commandRenderer->expects($this->once())
            ->method('render')
            ->with('pgrep', ['-f', $processName])
            ->willReturn($expectedCommand);

        $this->shell->expects($this->once())
            ->method('execute')
            ->with($expectedCommand)
            ->willThrowException(new \Exception('Command failed'));

        $result = $this->processMonitor->checkProcess($processName);
        $this->assertFalse($result);
    }

    /**
     * Тест получения информации о процессе
     */
    public function testGetProcessInfoWithValidProcess(): void
    {
        $processName = 'php-fpm';
        $expectedCommand = 'ps aux | grep php-fpm | grep -v grep';
        $expectedOutput = "www-data 1234 0.5 2.0 123456 7890 ? Ss 10:00 0:00 php-fpm: master process\n" .
                         "www-data 5678 0.0 1.0 98765 4321 ? S 10:00 0:00 php-fpm: pool www";

        $this->commandRenderer->expects($this->once())
            ->method('render')
            ->with('ps', ['aux', '|', 'grep', $processName, '|', 'grep', '-v', 'grep'])
            ->willReturn($expectedCommand);

        $this->shell->expects($this->once())
            ->method('execute')
            ->with($expectedCommand)
            ->willReturn($expectedOutput);

        $result = $this->processMonitor->getProcessInfo($processName);
        
        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        
        $this->assertEquals('1234', $result[0]['pid']);
        $this->assertEquals('www-data', $result[0]['user']);
        $this->assertEquals('0.5', $result[0]['cpu']);
        $this->assertEquals('2.0', $result[0]['memory']);
        $this->assertEquals('10:00 0:00', $result[0]['start_time']);
        $this->assertStringContainsString('php-fpm: master process', $result[0]['command']);
    }

    public function testGetProcessInfoWithNoProcesses(): void
    {
        $processName = 'non-existent-process';
        $expectedCommand = 'ps aux | grep non-existent-process | grep -v grep';

        $this->commandRenderer->expects($this->once())
            ->method('render')
            ->with('ps', ['aux', '|', 'grep', $processName, '|', 'grep', '-v', 'grep'])
            ->willReturn($expectedCommand);

        $this->shell->expects($this->once())
            ->method('execute')
            ->with($expectedCommand)
            ->willReturn('');

        $result = $this->processMonitor->getProcessInfo($processName);
        
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testGetProcessInfoWhenCommandFails(): void
    {
        $processName = 'php-fpm';
        $expectedCommand = 'ps aux | grep php-fpm | grep -v grep';

        $this->commandRenderer->expects($this->once())
            ->method('render')
            ->with('ps', ['aux', '|', 'grep', $processName, '|', 'grep', '-v', 'grep'])
            ->willReturn($expectedCommand);

        $this->shell->expects($this->once())
            ->method('execute')
            ->with($expectedCommand)
            ->willThrowException(new \Exception('Command failed'));

        $result = $this->processMonitor->getProcessInfo($processName);
        
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testGetProcessInfoWithInvalidFormat(): void
    {
        $processName = 'php-fpm';
        $expectedCommand = 'ps aux | grep php-fpm | grep -v grep';
        $invalidOutput = "invalid format output";

        $this->commandRenderer->expects($this->once())
            ->method('render')
            ->with('ps', ['aux', '|', 'grep', $processName, '|', 'grep', '-v', 'grep'])
            ->willReturn($expectedCommand);

        $this->shell->expects($this->once())
            ->method('execute')
            ->with($expectedCommand)
            ->willReturn($invalidOutput);

        $result = $this->processMonitor->getProcessInfo($processName);
        
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testGetProcessInfoWithSpecialCharacters(): void
    {
        $processName = 'php-fpm: pool www';
        $expectedCommand = 'ps aux | grep "php-fpm: pool www" | grep -v grep';
        $expectedOutput = "www-data 1234 0.5 2.0 123456 7890 ? Ss 10:00 0:00 php-fpm: pool www";

        $this->commandRenderer->expects($this->once())
            ->method('render')
            ->with('ps', ['aux', '|', 'grep', $processName, '|', 'grep', '-v', 'grep'])
            ->willReturn($expectedCommand);

        $this->shell->expects($this->once())
            ->method('execute')
            ->with($expectedCommand)
            ->willReturn($expectedOutput);

        $result = $this->processMonitor->getProcessInfo($processName);
        
        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertEquals('1234', $result[0]['pid']);
        $this->assertStringContainsString('php-fpm: pool www', $result[0]['command']);
    }

    public function testCheckProcessWithSpecialCharacters(): void
    {
        $processName = 'php-fpm: pool www';
        $expectedCommand = 'pgrep -f "php-fpm: pool www"';
        $expectedOutput = "1234";

        $this->commandRenderer->expects($this->once())
            ->method('render')
            ->with('pgrep', ['-f', $processName])
            ->willReturn($expectedCommand);

        $this->shell->expects($this->once())
            ->method('execute')
            ->with($expectedCommand)
            ->willReturn($expectedOutput);

        $result = $this->processMonitor->checkProcess($processName);
        $this->assertTrue($result);
    }
} 