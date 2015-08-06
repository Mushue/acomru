<?php

namespace KoolKode\Context\Locator;

use KoolKode\Context\Bind\Binding;
use KoolKode\Context\Container;

/**
 * @covers \KoolKode\Context\Locator\BindingServiceLocator
 */
class BindingServiceLocatorTest extends \PHPUnit_Framework_TestCase
{
    public function provideShared()
    {
        return [
            [false],
            [true]
        ];
    }

    /**
     * @dataProvider provideShared
     */
    public function testCanCreateEmptyLocator($shared)
    {
        $locator = new BindingServiceLocator(new Container(), $shared);

        $this->assertCount(0, $locator);
        $this->assertEquals([], iterator_to_array($locator->getIterator()));
    }

    public function testCanDebugServiceKeys()
    {
        $locator = new BindingServiceLocator(new Container());
        $locator->registerService(new Binding('stdClass'), 'foo');
        $locator->registerService(new Binding('stdClass'), 'bar');

        $this->assertEquals(['services' => ['foo', 'bar']], $locator->__debugInfo());
    }

    /**
     * @dataProvider provideShared
     */
    public function testCanRetrieveServiceUsingTypeName($shared)
    {
        $locator = new BindingServiceLocator(new Container(), $shared);
        $locator->registerService(new Binding('stdClass'));

        $this->assertTrue($locator->getService('stdClass') instanceof \stdClass);
    }

    /**
     * @dataProvider provideShared
     */
    public function testCanRetrieveServicUsingAlias($shared)
    {
        $container = new Container();
        $container->bindInstance('stdClass', new \stdClass());

        $locator = new BindingServiceLocator($container, $shared);
        $locator->registerService(new Binding('stdClass'), 'foo');

        $this->assertTrue($locator->getService('foo') instanceof \stdClass);
    }

    /**
     * @dataProvider provideShared
     */
    public function testCanIterateOverServicesWithAlias($shared)
    {
        $container = new Container();
        $foo = $container->bindInstance('stdClass', new \stdClass());

        $locator = new BindingServiceLocator($container, $shared);
        $locator->registerService(new Binding('stdClass'), 'foo');

        $this->assertCount(1, $locator);
        $this->assertEquals(['foo' => $foo], iterator_to_array($locator->getIterator()));
    }

    /**
     * @dataProvider provideShared
     * @expectedException \KoolKode\Context\Locator\ServiceNotFoundException
     */
    public function testThrowsExceptionWhenServiceIsNotBound($shared)
    {
        (new BindingServiceLocator(new Container(), $shared))->getService('foo');
    }

    /**
     * @expectedException \KoolKode\Context\Locator\DuplicateServiceRegistrationException
     */
    public function testThrowsExceptionWhenServiceIsRegisteredTwice()
    {
        $locator = new BindingServiceLocator(new Container());
        $locator->registerService(new Binding(''), 'foo');

        $locator->registerService(new Binding(''), 'foo');
    }
}
