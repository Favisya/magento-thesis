<?php
declare(strict_types=1);

/**
 * Тест для монитора RabbitMQ
 */

namespace Lachestry\RabbitMQMonitor\Test\Unit\Model;

use PHPUnit\Framework\TestCase;
use Lachestry\RabbitMQMonitor\Model\RabbitMQMonitor;
use Lachestry\Configuration\Model\Config;
use Magento\Framework\Shell;
use Magento\Framework\Shell\CommandRendererInterface;
use Magento\Framework\Exception\LocalizedException;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @covers \Lachestry\RabbitMQMonitor\Model\RabbitMQMonitor
 */
class RabbitMQMonitorTest extends TestCase
{
    /**
     * @var RabbitMQMonitor
     */
    protected RabbitMQMonitor $rabbitMQMonitor;

    /**
     * @var Config|MockObject
     */
    protected Config|MockObject $config;

    /**
     * @var Shell|MockObject
     */
    protected Shell|MockObject $shell;

    /**
     * @var CommandRendererInterface|MockObject
     */
    protected CommandRendererInterface|MockObject $commandRenderer;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->config          = $this->createMock(Config::class);
        $this->shell           = $this->createMock(Shell::class);
        $this->commandRenderer = $this->createMock(CommandRendererInterface::class);

        $this->rabbitMQMonitor = new RabbitMQMonitor(
            $this->config,
            $this->shell,
            $this->commandRenderer
        );
    }

    /**
     * Тест проверки состояния очереди
     */
    public function testCheckQueueStatusWithValidQueue(): void
    {
        $queueName        = 'test_queue';
        $expectedCommand  = 'curl -s -u guest:guest http://localhost:15672/api/queues/%2F/test_queue';
        $expectedResponse = json_encode([
            'name'      => 'test_queue',
            'messages'  => 100,
            'consumers' => 2,
            'memory'    => 1024,
            'state'     => 'running',
        ]);

        $this->config->method('getConfigValue')
            ->willReturnCallback(function ($path, $scope) {
                $defaults = [
                    'lachestry/rabbitmq/host'     => 'localhost',
                    'lachestry/rabbitmq/port'     => '15672',
                    'lachestry/rabbitmq/user'     => 'guest',
                    'lachestry/rabbitmq/password' => 'guest',
                    'lachestry/rabbitmq/vhost'    => '/',
                ];
                return $defaults[$path] ?? '';
            });

        $this->commandRenderer->expects($this->once())
            ->method('render')
            ->with('curl', $this->anything())
            ->willReturn($expectedCommand);

        $this->shell->expects($this->once())
            ->method('execute')
            ->with($expectedCommand)
            ->willReturn($expectedResponse);

        $result = $this->rabbitMQMonitor->checkQueueStatus($queueName);

        $this->assertIsArray($result);
        $this->assertEquals('test_queue', $result['name']);
        $this->assertEquals(100, $result['messages']);
        $this->assertEquals(2, $result['consumers']);
        $this->assertEquals(1024, $result['memory']);
        $this->assertEquals('running', $result['state']);
    }

    public function testCheckQueueStatusWithInvalidResponse(): void
    {
        $queueName       = 'test_queue';
        $expectedCommand = 'curl -s -u guest:guest http://localhost:15672/api/queues/%2F/test_queue';

        $this->config->method('getConfigValue')
            ->willReturnCallback(function ($path, $scope) {
                $defaults = [
                    'lachestry/rabbitmq/host'     => 'localhost',
                    'lachestry/rabbitmq/port'     => '15672',
                    'lachestry/rabbitmq/user'     => 'guest',
                    'lachestry/rabbitmq/password' => 'guest',
                    'lachestry/rabbitmq/vhost'    => '/',
                ];
                return $defaults[$path] ?? '';
            });

        $this->commandRenderer->expects($this->once())
            ->method('render')
            ->with('curl', $this->anything())
            ->willReturn($expectedCommand);

        $this->shell->expects($this->once())
            ->method('execute')
            ->with($expectedCommand)
            ->willReturn('invalid json');

        $result = $this->rabbitMQMonitor->checkQueueStatus($queueName);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('error', $result);
        $this->assertEquals('error', $result['state']);
    }

    public function testCheckQueueStatusWithCommandFailure(): void
    {
        $queueName       = 'test_queue';
        $expectedCommand = 'curl -s -u guest:guest http://localhost:15672/api/queues/%2F/test_queue';

        $this->config->method('getConfigValue')
            ->willReturnCallback(function ($path, $scope) {
                $defaults = [
                    'lachestry/rabbitmq/host'     => 'localhost',
                    'lachestry/rabbitmq/port'     => '15672',
                    'lachestry/rabbitmq/user'     => 'guest',
                    'lachestry/rabbitmq/password' => 'guest',
                    'lachestry/rabbitmq/vhost'    => '/',
                ];
                return $defaults[$path] ?? '';
            });

        $this->commandRenderer->expects($this->once())
            ->method('render')
            ->with('curl', $this->anything())
            ->willReturn($expectedCommand);

        $this->shell->expects($this->once())
            ->method('execute')
            ->with($expectedCommand)
            ->willThrowException(new \Exception('Connection failed'));

        $result = $this->rabbitMQMonitor->checkQueueStatus($queueName);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('error', $result);
        $this->assertEquals('error', $result['state']);
    }

    public function testCheckQueueStatusWithHttpError(): void
    {
        $queueName       = 'test_queue';
        $expectedCommand = 'curl -s -u guest:guest http://localhost:15672/api/queues/%2F/test_queue';

        $this->config->method('getConfigValue')
            ->willReturnCallback(function ($path, $scope) {
                $defaults = [
                    'lachestry/rabbitmq/host'     => 'localhost',
                    'lachestry/rabbitmq/port'     => '15672',
                    'lachestry/rabbitmq/user'     => 'guest',
                    'lachestry/rabbitmq/password' => 'guest',
                    'lachestry/rabbitmq/vhost'    => '/',
                ];
                return $defaults[$path] ?? '';
            });

        $this->commandRenderer->expects($this->once())
            ->method('render')
            ->with('curl', $this->anything())
            ->willReturn($expectedCommand);

        $this->shell->expects($this->once())
            ->method('execute')
            ->with($expectedCommand)
            ->willReturn('{"error": "Not Found", "reason": "Object Not Found"}');

        $result = $this->rabbitMQMonitor->checkQueueStatus($queueName);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('error', $result);
        $this->assertEquals('error', $result['state']);
    }

    public function testCheckQueueStatusWithTimeout(): void
    {
        $queueName       = 'test_queue';
        $expectedCommand = 'curl -s -u guest:guest http://localhost:15672/api/queues/%2F/test_queue';

        $this->config->method('getConfigValue')
            ->willReturnCallback(function ($path, $scope) {
                $defaults = [
                    'lachestry/rabbitmq/host'     => 'localhost',
                    'lachestry/rabbitmq/port'     => '15672',
                    'lachestry/rabbitmq/user'     => 'guest',
                    'lachestry/rabbitmq/password' => 'guest',
                    'lachestry/rabbitmq/vhost'    => '/',
                ];
                return $defaults[$path] ?? '';
            });

        $this->commandRenderer->expects($this->once())
            ->method('render')
            ->with('curl', $this->anything())
            ->willReturn($expectedCommand);

        $this->shell->expects($this->once())
            ->method('execute')
            ->with($expectedCommand)
            ->willThrowException(new \Exception('Connection timed out'));

        $result = $this->rabbitMQMonitor->checkQueueStatus($queueName);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('error', $result);
        $this->assertEquals('error', $result['state']);
    }

    /**
     * Тест очистки очереди
     */
    public function testPurgeQueueSuccess(): void
    {
        $queueName       = 'test_queue';
        $expectedCommand = 'curl -s -X DELETE -u guest:guest http://localhost:15672/api/queues/%2F/test_queue/contents';

        $this->config->method('getConfigValue')
            ->willReturnCallback(function ($path, $scope) {
                $defaults = [
                    'lachestry/rabbitmq/host'     => 'localhost',
                    'lachestry/rabbitmq/port'     => '15672',
                    'lachestry/rabbitmq/user'     => 'guest',
                    'lachestry/rabbitmq/password' => 'guest',
                    'lachestry/rabbitmq/vhost'    => '/',
                ];
                return $defaults[$path] ?? '';
            });

        $this->commandRenderer->expects($this->once())
            ->method('render')
            ->with('curl', $this->anything())
            ->willReturn($expectedCommand);

        $this->shell->expects($this->once())
            ->method('execute')
            ->with($expectedCommand);

        $this->rabbitMQMonitor->purgeQueue($queueName);
    }

    public function testPurgeQueueFailure(): void
    {
        $queueName       = 'test_queue';
        $expectedCommand = 'curl -s -X DELETE -u guest:guest http://localhost:15672/api/queues/%2F/test_queue/contents';

        $this->config->method('getConfigValue')
            ->willReturnCallback(function ($path, $scope) {
                $defaults = [
                    'lachestry/rabbitmq/host'     => 'localhost',
                    'lachestry/rabbitmq/port'     => '15672',
                    'lachestry/rabbitmq/user'     => 'guest',
                    'lachestry/rabbitmq/password' => 'guest',
                    'lachestry/rabbitmq/vhost'    => '/',
                ];
                return $defaults[$path] ?? '';
            });

        $this->commandRenderer->expects($this->once())
            ->method('render')
            ->with('curl', $this->anything())
            ->willReturn($expectedCommand);

        $this->shell->expects($this->once())
            ->method('execute')
            ->with($expectedCommand)
            ->willThrowException(new \Exception('Connection failed'));

        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('Failed to purge queue: Connection failed');

        $this->rabbitMQMonitor->purgeQueue($queueName);
    }

    public function testPurgeQueueWithTimeout(): void
    {
        $queueName       = 'test_queue';
        $expectedCommand = 'curl -s -X DELETE -u guest:guest http://localhost:15672/api/queues/%2F/test_queue/contents';

        $this->config->method('getConfigValue')
            ->willReturnCallback(function ($path, $scope) {
                $defaults = [
                    'lachestry/rabbitmq/host'     => 'localhost',
                    'lachestry/rabbitmq/port'     => '15672',
                    'lachestry/rabbitmq/user'     => 'guest',
                    'lachestry/rabbitmq/password' => 'guest',
                    'lachestry/rabbitmq/vhost'    => '/',
                ];
                return $defaults[$path] ?? '';
            });

        $this->commandRenderer->expects($this->once())
            ->method('render')
            ->with('curl', $this->anything())
            ->willReturn($expectedCommand);

        $this->shell->expects($this->once())
            ->method('execute')
            ->with($expectedCommand)
            ->willThrowException(new \Exception('Connection timed out'));

        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('Failed to purge queue: Connection timed out');

        $this->rabbitMQMonitor->purgeQueue($queueName);
    }
}
