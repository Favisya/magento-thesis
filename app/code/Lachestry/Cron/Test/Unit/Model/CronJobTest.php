<?php
declare(strict_types=1);

/**
 * Тест для крон-задач
 */
namespace Lachestry\Cron\Test\Unit\Model;

use PHPUnit\Framework\TestCase;
use Lachestry\Cron\Model\CronJob;
use Lachestry\Configuration\Model\Config;
use Lachestry\LogMonitor\Model\LogMonitor;
use Lachestry\ProcessMonitor\Model\ProcessMonitor;
use Lachestry\RabbitMQMonitor\Model\RabbitMQMonitor;
use Lachestry\Notifier\Model\Notification;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @covers \Lachestry\Cron\Model\CronJob
 */
class CronJobTest extends TestCase
{
    /**
     * @var CronJob
     */
    protected CronJob $cronJob;

    /**
     * @var Config|\PHPUnit\Framework\MockObject\MockObject
     */
    protected Config|MockObject $config;

    /**
     * @var LogMonitor|\PHPUnit\Framework\MockObject\MockObject
     */
    protected LogMonitor|MockObject $logMonitor;

    /**
     * @var ProcessMonitor|\PHPUnit\Framework\MockObject\MockObject
     */
    protected ProcessMonitor|MockObject $processMonitor;

    /**
     * @var RabbitMQMonitor|\PHPUnit\Framework\MockObject\MockObject
     */
    protected RabbitMQMonitor|MockObject $rabbitMQMonitor;

    /**
     * @var Notification|\PHPUnit\Framework\MockObject\MockObject
     */
    protected Notification|MockObject $notification;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->config = $this->createMock(Config::class);
        $this->logMonitor = $this->createMock(LogMonitor::class);
        $this->processMonitor = $this->createMock(ProcessMonitor::class);
        $this->rabbitMQMonitor = $this->createMock(RabbitMQMonitor::class);
        $this->notification = $this->createMock(Notification::class);
        
        $this->cronJob = new CronJob(
            $this->config,
            $this->logMonitor,
            $this->processMonitor,
            $this->rabbitMQMonitor,
            $this->notification
        );
    }

    /**
     * Тест выполнения крон-задачи
     */
    public function testExecute(): void
    {
        $this->config->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);

        $this->cronJob->execute();
    }

    /**
     * Тест пропуска выполнения крон-задачи
     */
    public function testExecuteDisabled(): void
    {
        $this->config->expects($this->once())
            ->method('isEnabled')
            ->willReturn(false);

        $this->logMonitor->expects($this->never())
            ->method('checkLogForErrors');

        $this->processMonitor->expects($this->never())
            ->method('checkProcess');

        $this->rabbitMQMonitor->expects($this->never())
            ->method('checkQueueStatus');

        $this->notification->expects($this->never())
            ->method('sendNotification');

        $this->cronJob->execute();
    }

    public function testExecuteWithNoIssues(): void
    {
        $this->config->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);

        $this->logMonitor->expects($this->once())
            ->method('checkLogForErrors')
            ->willReturn([]);

        $this->processMonitor->expects($this->exactly(3))
            ->method('checkProcess')
            ->willReturnMap([
                ['php-fpm', true],
                ['nginx', true],
                ['mysql', true]
            ]);

        $this->rabbitMQMonitor->expects($this->exactly(2))
            ->method('checkQueueStatus')
            ->willReturnMap([
                ['catalog_product_import', ['messages' => 100]],
                ['catalog_product_export', ['messages' => 200]]
            ]);

        $this->notification->expects($this->never())
            ->method('sendNotification');

        $this->cronJob->execute();
    }

    public function testExecuteWithLogErrors(): void
    {
        $this->config->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);

        $this->logMonitor->expects($this->once())
            ->method('checkLogForErrors')
            ->willReturn([
                ['message' => 'Error 1'],
                ['message' => 'Error 2']
            ]);

        $this->processMonitor->expects($this->exactly(3))
            ->method('checkProcess')
            ->willReturnMap([
                ['php-fpm', true],
                ['nginx', true],
                ['mysql', true]
            ]);

        $this->rabbitMQMonitor->expects($this->exactly(2))
            ->method('checkQueueStatus')
            ->willReturnMap([
                ['catalog_product_import', ['messages' => 100]],
                ['catalog_product_export', ['messages' => 200]]
            ]);

        $this->notification->expects($this->once())
            ->method('sendNotification')
            ->with($this->stringContains('Found errors in logs: 2 entries'));

        $this->cronJob->execute();
    }

    public function testExecuteWithProcessIssues(): void
    {
        $this->config->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);

        $this->logMonitor->expects($this->once())
            ->method('checkLogForErrors')
            ->willReturn([]);

        $this->processMonitor->expects($this->exactly(3))
            ->method('checkProcess')
            ->willReturnMap([
                ['php-fpm', true],
                ['nginx', false],
                ['mysql', true]
            ]);

        $this->rabbitMQMonitor->expects($this->exactly(2))
            ->method('checkQueueStatus')
            ->willReturnMap([
                ['catalog_product_import', ['messages' => 100]],
                ['catalog_product_export', ['messages' => 200]]
            ]);

        $this->notification->expects($this->once())
            ->method('sendNotification')
            ->with($this->stringContains('Process nginx is not running'));

        $this->cronJob->execute();
    }

    public function testExecuteWithRabbitMQIssues(): void
    {
        $this->config->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);

        $this->logMonitor->expects($this->once())
            ->method('checkLogForErrors')
            ->willReturn([]);

        $this->processMonitor->expects($this->exactly(3))
            ->method('checkProcess')
            ->willReturnMap([
                ['php-fpm', true],
                ['nginx', true],
                ['mysql', true]
            ]);

        $this->rabbitMQMonitor->expects($this->exactly(2))
            ->method('checkQueueStatus')
            ->willReturnMap([
                ['catalog_product_import', ['error' => 'Connection failed']],
                ['catalog_product_export', ['messages' => 2000]]
            ]);

        $this->notification->expects($this->once())
            ->method('sendNotification')
            ->with($this->callback(function ($message) {
                return strpos($message, 'RabbitMQ queue catalog_product_import error: Connection failed') !== false
                    && strpos($message, 'RabbitMQ queue catalog_product_export has too many messages: 2000') !== false;
            }));

        $this->cronJob->execute();
    }
} 