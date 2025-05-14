<?php
declare(strict_types=1);

namespace Lachestry\Test;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase as BaseTestCase;
use PHPUnit\Framework\MockObject\MockObject;

abstract class TestCase extends BaseTestCase
{
    protected ObjectManager $objectManager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->objectManager = new ObjectManager($this);
    }

    protected function getObject(string $class, array $arguments = []): object
    {
        return $this->objectManager->getObject($class, $arguments);
    }

    protected function createMock(string $originalClassName): MockObject
    {
        return $this->getMockBuilder($originalClassName)
            ->disableOriginalConstructor()
            ->getMock();
    }
} 