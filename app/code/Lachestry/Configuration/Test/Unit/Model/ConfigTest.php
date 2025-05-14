<?php
declare(strict_types=1);

namespace Lachestry\Configuration\Test\Unit\Model;

use PHPUnit\Framework\TestCase;
use Lachestry\Configuration\Model\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @covers \Lachestry\Configuration\Model\Config
 */
class ConfigTest extends TestCase
{
    protected Config $config;
    protected ScopeConfigInterface|MockObject $scopeConfig;

    protected function setUp(): void
    {
        $this->scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $this->config = new Config($this->scopeConfig);
    }

    /**
     * @covers \Lachestry\Configuration\Model\Config::getConfigValue
     */
    public function testGetConfigValue(): void
    {
        $path = 'lachestry/general/enabled';
        $scope = 'store';
        $scopeId = 1;
        $expectedValue = '1';

        $this->scopeConfig->expects($this->once())
            ->method('getValue')
            ->with($path, $scope, $scopeId)
            ->willReturn($expectedValue);

        $result = $this->config->getConfigValue($path, $scope, $scopeId);
        $this->assertEquals($expectedValue, $result);
    }

    public function testGetConfigValueWithDefaultScope(): void
    {
        $path = 'lachestry/general/api_key';
        $expectedValue = 'test_api_key';

        $this->scopeConfig->expects($this->once())
            ->method('getValue')
            ->with($path, 'store', null)
            ->willReturn($expectedValue);

        $result = $this->config->getConfigValue($path);
        $this->assertEquals($expectedValue, $result);
    }

    public function testGetConfigValueNull(): void
    {
        $path = 'lachestry/general/non_existent';

        $this->scopeConfig->expects($this->once())
            ->method('getValue')
            ->with($path, 'store', null)
            ->willReturn(null);

        $result = $this->config->getConfigValue($path);
        $this->assertEquals('', $result);
    }

    /**
     * @covers \Lachestry\Configuration\Model\Config::isEnabled
     */
    public function testIsEnabled(): void
    {
        $this->scopeConfig->expects($this->once())
            ->method('isSetFlag')
            ->with('lachestry/general/enabled')
            ->willReturn(true);

        $result = $this->config->isEnabled();
        $this->assertTrue($result);
    }

    public function testIsDisabled(): void
    {
        $this->scopeConfig->expects($this->once())
            ->method('isSetFlag')
            ->with('lachestry/general/enabled')
            ->willReturn(false);

        $result = $this->config->isEnabled();
        $this->assertFalse($result);
    }
} 