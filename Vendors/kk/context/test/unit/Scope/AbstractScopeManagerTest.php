<?php

namespace KoolKode\Context\Scope;

use KoolKode\Context\Bind\Binding;
use KoolKode\Context\Container;

/**
 * @covers \KoolKode\Context\Scope\AbstractScopeManager
 * @covers \KoolKode\Context\Scope\ScopedProxyTrait
 */
class AbstratScopeManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AbstractScope
     */
    protected $scope;

    public function testCreationOfEmptyScope()
    {
        $this->assertNull($this->scope->getContainer());
        $this->assertEquals([], $this->scope->getProxyTypeNames());
        $this->assertFalse($this->scope->isActive());
    }

    public function testCanCorrelateScopeToContainer()
    {
        $container = new Container();

        $this->assertNull($this->scope->getContainer());
        $this->scope->correlate($container);
        $this->assertSame($container, $this->scope->getContainer());
        $this->assertSame($container->get(get_class($this->scope)), $this->scope);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testCannotSerializeScope()
    {
        serialize($this->scope);
    }

    public function testScopeActivation()
    {
        $scope = new TestScope();
        $this->assertFalse($scope->isActive());

        $scope->enter(new \stdClass());
        $this->assertTrue($scope->isActive());
    }

    /**
     * @expectedException \KoolKode\Context\Scope\ScopeNotActiveException
     */
    public function testCannotActivateInstanceWhenScopeIsNotActive()
    {
        $this->scope->activateInstance(new ScopedProxyMock('stdClass'));
    }

    /**
     * @expectedException \KoolKode\Context\TypeNotFoundException
     */
    public function testProxyGenerationThrowsExceptionOnMissingType()
    {
        $this->scope->createProxy(new Binding('@foo'));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testBindingContextRequiresObject()
    {
        (new TestScope())->enter('foo');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testUnbindingContextRequiresObject()
    {
        (new TestScope())->leave('bar');
    }

    /**
     * @expectedException \BadMethodCallException
     */
    public function testCannotLookupObjectInProxyScope()
    {
        (new TestScope())->lookup('Foo', function () {
        });
    }

    public function testCanCreateProxyWithCallback()
    {
        $scope = new TestScope();
        $container = $this->getMock('KoolKode\Context\Scope\ScopedContainerInterface');
        $container->expects($this->once())->method('loadScopedProxy')->will($this->returnValue('KoolKode\Context\Scope\TestProxy'));

        $scope->correlate($container);

        $factory = function () {
        };
        $proxy = $scope->initializeProxy('foobar', $factory);

        $this->assertTrue($proxy instanceof TestProxy);
        $this->assertEquals('foobar', $proxy->getBinding()->getTypeName());
        $this->assertSame($scope, $proxy->getScope());
        $this->assertSame($factory, $proxy->getBinding()->getCallback());
    }

    public function testCanCreateProxyWithoutCallback()
    {
        $scope = new TestScope();
        $container = $this->getMock('KoolKode\Context\Scope\ScopedContainerInterface');
        $container->expects($this->once())->method('loadScopedProxy')->will($this->returnValue('KoolKode\Context\Scope\TestProxy'));

        $scope->correlate($container);
        $proxy = $scope->initializeProxy('foobar');

        $this->assertTrue($proxy instanceof TestProxy);
        $this->assertEquals('foobar', $proxy->getBinding()->getTypeName());
        $this->assertSame($scope, $proxy->getScope());

        $callback = $proxy->getBinding()->getCallback();
        $this->assertTrue($callback instanceof \Closure);

        try {
            $callback();
        } catch (ScopeNotActiveException $e) {
            return;
        }

        $this->fail('ScopeNotActiveException not thrown from factory');
    }

    public function testLeavingScopeWillClearProxyInstances()
    {
        $context = new \stdClass();
        $binding = new Binding('KoolKode\Context\Scope\FoxyProxy');

        $scope = new TestScope();
        $scope->correlate(new Container());

        $this->assertFalse($scope->isActive());
        $this->assertNull($scope->enter($context));

        $proxy = $scope->createProxy($binding);
        $this->assertTrue($proxy instanceof ScopedProxyInterface);
        $this->assertFalse($proxy->K2IsProxyBound());
        $this->assertTrue($scope->isActive());

        $scope->leave($context);
        $this->assertFalse($proxy->K2IsProxyBound());
        $this->assertFalse($scope->isActive());
    }

    public function testWillReUseScopeProxyInstance()
    {
        $context = new \stdClass();
        $binding = new Binding(FoxyProxy::class);

        $scope = new TestScope();
        $scope->enter($context);

        $proxy1 = $scope->createProxy($binding);
        $proxy2 = $scope->createProxy($binding);
        $this->assertTrue($proxy1 instanceof ScopedProxyInterface);
        $this->assertTrue($proxy2 instanceof ScopedProxyInterface);
        $this->assertSame($proxy1, $proxy2);
    }

    public function testCanDeactivateScopeWillClearInstances()
    {
        $context = new \stdClass();
        $binding = new Binding(FoxyProxy::class);

        $scope = new TestScope();
        $scope->correlate(new Container());
        $scope->enter($context);

        $proxy = $scope->createProxy($binding);
        $this->assertTrue($proxy instanceof ScopedProxyInterface);
        $this->assertFalse($proxy->K2IsProxyBound());
        $this->assertSame($binding, $proxy->K2GetProxyBinding());

        $foxy = $proxy->K2UnwrapScopedProxy();
        $this->assertTrue($foxy instanceof FoxyProxy);
        $this->assertTrue($proxy->K2IsProxyBound());

        $scope->clear();
        $this->assertFalse($proxy->K2IsProxyBound());
    }

    public function testPushingScopeWillClearInstances()
    {
        $context1 = new \stdClass();
        $context2 = new \stdClass();
        $binding = new Binding(FoxyProxy::class);

        $scope = new TestScope();
        $scope->correlate(new Container());
        $scope->enter($context1);

        $proxy = $scope->createProxy($binding);
        $this->assertTrue($proxy instanceof ScopedProxyInterface);

        $proxy->K2UnwrapScopedProxy();
        $this->assertTrue($proxy->K2IsProxyBound());

        $scope->enter($context2);
        $this->assertFalse($proxy->K2IsProxyBound());

        $scope->leave($context2);
        $this->assertFalse($proxy->K2IsProxyBound());

        $scope->enter($context1);
        $this->assertTrue($proxy->K2IsProxyBound());
    }

    public function testCanUnbindAndReBindContext()
    {
        $context = new \stdClass();
        $binding = new Binding(FoxyProxy::class);

        $scope = new TestScope();
        $scope->correlate(new Container());
        $scope->enter($context);

        $proxy = $scope->createProxy($binding);
        $this->assertTrue($proxy instanceof ScopedProxyInterface);
        $this->assertFalse($proxy->K2IsProxyBound());

        $instance = $proxy->K2UnwrapScopedProxy();
        $this->assertTrue($proxy->K2IsProxyBound());

        $scope->enter(NULL);
        $this->assertFalse($proxy->K2IsProxyBound());

        $scope->enter($context);
        $this->assertTrue($proxy->K2IsProxyBound());
        $this->assertSame($instance, $proxy->K2UnwrapScopedProxy());
    }

    public function testCanUnbindWithoutTermination()
    {
        $context = new \stdClass();
        $binding = new Binding(FoxyProxy::class);

        $scope = new TestScope();
        $scope->correlate(new Container());
        $scope->enter($context);

        $proxy = $scope->createProxy($binding);
        $this->assertTrue($proxy instanceof ScopedProxyInterface);
        $this->assertFalse($proxy->K2IsProxyBound());

        $instance = $proxy->K2UnwrapScopedProxy();
        $this->assertTrue($proxy->K2IsProxyBound());

        $scope->leave($context, false);
        $this->assertFalse($proxy->K2IsProxyBound());

        $scope->enter($context);
        $this->assertTrue($proxy->K2IsProxyBound());
        $this->assertSame($instance, $proxy->K2UnwrapScopedProxy());

        $scope->leave($context);
        $this->assertFalse($proxy->K2IsProxyBound());

        $scope->enter($context);
        $this->assertFalse($proxy->K2IsProxyBound());
        $this->assertNotSame($instance, $proxy->K2UnwrapScopedProxy());
    }

    protected function setUp()
    {
        parent::setUp();

        $this->scope = $this->getMockForAbstractClass(AbstractScopeManager::class);
    }
}
