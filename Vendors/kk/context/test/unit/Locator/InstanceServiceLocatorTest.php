<?php

namespace KoolKode\Context\Locator;

/**
 * @covers \KoolKode\Context\Locator\InstanceServiceLocator
 */
class InstanceServiceLocatorTest extends \PHPUnit_Framework_TestCase
{
    public function testCanCreateEmptyLocator()
    {
        $locator = new InstanceServiceLocator();

        $this->assertCount(0, $locator);
        $this->assertEquals([], iterator_to_array($locator->getIterator()));
    }

    public function testCanDebugServiceKeys()
    {
        $locator = new InstanceServiceLocator();
        $locator->registerService(new \stdClass(), 'foo');
        $locator->registerService(new \stdClass(), 'bar');

        $this->assertEquals(['services' => ['foo', 'bar']], $locator->__debugInfo());
    }

    public function testCanRetrieveServiceUsingTypeName()
    {
        $locator = new InstanceServiceLocator();
        $foo = new \stdClass();
        $locator->registerService($foo);

        $this->assertSame($foo, $locator->getService(get_class($foo)));
        $this->assertCount(1, $locator);
        $this->assertEquals([get_class($foo) => $foo], iterator_to_array($locator->getIterator()));
    }

    public function testCanRetrieveServicUsingAlias()
    {
        $locator = new InstanceServiceLocator();
        $foo = new \stdClass();
        $locator->registerService($foo, 'foo');

        $this->assertSame($foo, $locator->getService('foo'));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testThrowsExceptionWhenAttemptingToRegisterNonObject()
    {
        (new InstanceServiceLocator())->registerService('hello');
    }

    /**
     * @expectedException \KoolKode\Context\Locator\ServiceNotFoundException
     */
    public function testThrowsExceptionWhenServiceIsNotBound()
    {
        (new InstanceServiceLocator())->getService('foo');
    }

    /**
     * @expectedException \KoolKode\Context\Locator\DuplicateServiceRegistrationException
     */
    public function testThrowsExceptionWhenServiceIsRegisteredTwice()
    {
        $locator = new InstanceServiceLocator();
        $locator->registerService(new \stdClass(), 'foo');

        $locator->registerService(new \stdClass(), 'foo');
    }
}
