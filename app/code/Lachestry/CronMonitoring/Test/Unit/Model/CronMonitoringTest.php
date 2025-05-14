<?php
declare(strict_types=1);

/**
 * Тест для мониторинга крон-задач
 */
namespace Lachestry\CronMonitoring\Test\Unit\Model;

use PHPUnit\Framework\TestCase;
use Lachestry\CronMonitoring\Model\CronMonitoring;
use Lachestry\Configuration\Model\Config;
use Magento\Cron\Model\ResourceModel\Schedule\Collection as ScheduleCollection;
use Magento\Cron\Model\ResourceModel\Schedule\CollectionFactory as ScheduleCollectionFactory;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @covers \Lachestry\CronMonitoring\Model\CronMonitoring
 */
class CronMonitoringTest extends TestCase
{
    /**
     * @var CronMonitoring
     */
    protected CronMonitoring $cronMonitoring;

    /**
     * @var Config|\PHPUnit\Framework\MockObject\MockObject
     */
    protected Config|MockObject $config;

    /**
     * @var ScheduleCollectionFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected ScheduleCollectionFactory|MockObject $scheduleCollectionFactory;

    /**
     * @var ScheduleCollection|\PHPUnit\Framework\MockObject\MockObject
     */
    protected ScheduleCollection|MockObject $scheduleCollection;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->config = $this->createMock(Config::class);
        $this->scheduleCollection = $this->createMock(ScheduleCollection::class);
        $this->scheduleCollectionFactory = $this->createMock(ScheduleCollectionFactory::class);
        
        $this->scheduleCollectionFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->scheduleCollection);
        
        $this->cronMonitoring = new CronMonitoring(
            $this->config,
            $this->scheduleCollectionFactory
        );
    }

    /**
     * Тест проверки статуса крон-задачи
     */
    public function testCheckCronStatus(): void
    {
        $this->config->expects($this->once())
            ->method('getConfigValue')
            ->with('lachestry/cron/monitor_period')
            ->willReturn('24');
            
        $this->scheduleCollection->expects($this->once())
            ->method('addFieldToFilter')
            ->willReturnSelf();
            
        $this->scheduleCollection->expects($this->once())
            ->method('getItems')
            ->willReturn([]);

        $result = $this->cronMonitoring->checkCronStatus();
        
        $this->assertIsArray($result);
    }

    /**
     * Тест получения списка проблемных крон-задач
     */
    public function testGetFailedJobs(): void
    {
        $this->config->expects($this->once())
            ->method('getConfigValue')
            ->with('lachestry/cron/monitor_period')
            ->willReturn('24');
            
        $this->scheduleCollection->expects($this->exactly(2))
            ->method('addFieldToFilter')
            ->willReturnSelf();
            
        $this->scheduleCollection->expects($this->once())
            ->method('getItems')
            ->willReturn([]);

        $result = $this->cronMonitoring->getFailedJobs();
        
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }
    
    public function testGetFailedJobsEmpty(): void
    {
        $this->config->expects($this->once())
            ->method('getConfigValue')
            ->with('lachestry/cron/monitor_period')
            ->willReturn('24');
            
        $this->scheduleCollection->expects($this->exactly(2))
            ->method('addFieldToFilter')
            ->willReturnSelf();
            
        $this->scheduleCollection->expects($this->once())
            ->method('getItems')
            ->willReturn([]);

        $result = $this->cronMonitoring->getFailedJobs();
        
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }
} 