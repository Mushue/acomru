<?php

/*
 * This file is part of KoolKode Context and Dependency Injection.
*
* (c) Martin Schröder <m.schroeder2007@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace KoolKode\Context\Bind;

use KoolKode\Context\Scope\ApplicationScoped;
use KoolKode\Context\Scope\Dependent;
use KoolKode\Context\Scope\Scope;
use KoolKode\Context\Scope\Singleton;

/**
 * @covers \KoolKode\Context\Bind\Binding
 * @covers \KoolKode\Context\Bind\AbstractBinding
 */
class BindingTest extends \PHPUnit_Framework_TestCase
{
    public function testDefaultsToDependentImplementationBinding()
    {
        $binding = new Binding('stdClass');

        $this->assertEquals('stdClass', (string)$binding);
        $this->assertEquals('stdClass', $binding->getTypeName());
        $this->assertEquals('stdClass', $binding->getTarget());
        $this->assertEquals(BindingInterface::TYPE_IMPLEMENTATION, $binding->getOptions());
        $this->assertEquals(BindingInterface::TYPE_IMPLEMENTATION, $binding->getType());
        $this->assertNull($binding->getScope());

        $this->assertFalse($binding->isAliasBinding());
        $this->assertFalse($binding->isFactoryBinding());
        $this->assertTrue($binding->isImplementationBinding());

        $this->assertEquals([
            'typeName' => \stdClass::class,
            'scope' => NULL,
            'options' => BindingInterface::TYPE_IMPLEMENTATION,
            'to' => NULL,
            'markers' => [],
            'resolvers' => [],
            'initializerCount' => 0,
            'decoratorCount' => 0
        ], $binding->__debugInfo());
    }

    public function testInvokeBinding()
    {
        $binding = new Binding('stdClass');
        $std = new \stdClass();

        $container = $this->getMock('KoolKode\Context\Container');
        $container->expects($this->once())->method('createInstance')->will($this->returnValue($std));

        $this->assertSame($std, $binding($container));
    }

    public function testCanSetBindingDefaultsExplicitly()
    {
        $b1 = new Binding('foo');

        $b2 = new Binding('foo');
        $b2->to('foo');
        $b2->scoped(new Dependent());

        $this->assertEquals($b1, $b2);
    }

    public function testCanCreateScopedFactoryBinding()
    {
        $binding = (new Binding('stdClass'))->to(function () {
        })->scoped(new ApplicationScoped());
        $options = BindingInterface::TYPE_FACTORY;

        $this->assertTrue($binding->isFactoryBinding());
        $this->assertEquals('stdClass', $binding->getTypeName());
        $this->assertEquals($options, $binding->getOptions());
        $this->assertTrue($binding->getTarget() instanceof \Closure);
        $this->assertEquals(BindingInterface::TYPE_FACTORY, $binding->getType());
        $this->assertEquals(ApplicationScoped::class, $binding->getScope());

        $debug = $binding->__debugInfo();
        $this->assertEquals('*closure*', $debug['to']);
    }

    public function testCanCreateScopedAliasBinding()
    {
        $binding = (new Binding('stdClass'))->toAlias('foo')->scoped(new Singleton());
        $options = BindingInterface::TYPE_ALIAS;

        $this->assertTrue($binding->isAliasBinding());
        $this->assertEquals('stdClass', $binding->getTypeName());
        $this->assertEquals('foo', $binding->getTarget());
        $this->assertEquals($options, $binding->getOptions());
        $this->assertEquals(BindingInterface::TYPE_ALIAS, $binding->getType());
        $this->assertEquals(Singleton::class, $binding->getScope());

        $debug = $binding->__debugInfo();
        $this->assertEquals('foo', $debug['to']);
    }

    public function testCanCreateFactoryAliasBinding()
    {
        $binding = (new Binding('stdClass'))->to(__CLASS__, 'createObject')->scoped(new ApplicationScoped());
        $options = BindingInterface::TYPE_FACTORY_ALIAS;

        $this->assertTrue($binding->isFactoryAliasBinding());
        $this->assertEquals('stdClass', $binding->getTypeName());
        $this->assertEquals($options, $binding->getOptions());
        $this->assertEquals([__CLASS__, 'createObject'], $binding->getTarget());
        $this->assertEquals(BindingInterface::TYPE_FACTORY_ALIAS, $binding->getType());
        $this->assertEquals(ApplicationScoped::class, $binding->getScope());

        $debug = $binding->__debugInfo();
        $this->assertEquals([__CLASS__, 'createObject'], $debug['to']);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCannotSetMethodNameInInlineFactoryBinding()
    {
        (new Binding('stdClass'))->to(function () {
        }, 'create');
    }

    public function testCanCreateSingletonBinding()
    {
        $binding = (new Binding('stdClass'))->scoped(new Singleton());
        $options = BindingInterface::TYPE_IMPLEMENTATION;

        $this->assertEquals('stdClass', $binding->getTypeName());
        $this->assertEquals($options, $binding->getOptions());
        $this->assertEquals(BindingInterface::TYPE_IMPLEMENTATION, $binding->getType());
        $this->assertEquals(Singleton::class, $binding->getScope());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCannotBindToObject()
    {
        (new Binding('stdClass'))->to(new \stdClass());
    }

    public function testCanAttachMarkersToBinding()
    {
        $binding = new Binding('stdClass');
        $this->assertCount(0, $binding->getMarkers());
        $this->assertFalse($binding->isMarked('foo'));

        $binding->marked(new TestMarker());
        $binding->marked(new SetterInjection());

        $markers = $binding->getMarkers();
        $this->assertCount(2, $markers);
        $this->assertTrue($markers[0] instanceof TestMarker);
        $this->assertTrue($markers[1] instanceof SetterInjection);

        $this->assertFalse($binding->isMarked(new \ReflectionClass('stdClass')));
        $this->assertFalse($binding->isMarked(new FooMarker('foo!')));
        $this->assertFalse($binding->isMarked('stdClass'));

        $this->assertTrue($binding->isMarked(new \ReflectionClass('KoolKode\Context\Bind\TestMarker')));
        $this->assertTrue($binding->isMarked(new TestMarker()));
        $this->assertTrue($binding->isMarked('KoolKode\Context\Bind\TestMarker'));
    }

    public function testCanAccessBindingMarkers()
    {
        $test = new TestMarker();
        $setter = new SetterInjection();

        $binding = new Binding('stdClass');
        $binding->marked($test);
        $binding->marked($setter);

        $this->assertSame($test, $binding->getMarker(get_class($test)));
        $this->assertNull($binding->getMarker('FooBar', NULL));

        $result = $binding->getMarkers(get_class($setter));
        $this->assertCount(1, $result);
        $this->assertSame($setter, $result[0]);
    }

    /**
     * @expectedException \OutOfBoundsException
     */
    public function testAccessMarkerThrowsExceptionWhenNotFound()
    {
        (new Binding('stdClass'))->getMarker('FooBar');
    }

    public function testCanAddResolvers()
    {
        $binding = new Binding('stdClass');
        $this->assertNull($binding->getResolvers());

        $binding->resolve('foo', 'bar');
        $this->assertEquals(['foo' => 'bar'], $binding->getResolvers());

        $binding->resolve('hello', 'world');
        $this->assertEquals(['foo' => 'bar', 'hello' => 'world'], $binding->getResolvers());

        $resolver = function () {
        };

        $binding->resolve('foo', $resolver);
        $this->assertEquals(['foo' => $resolver, 'hello' => 'world'], $binding->getResolvers());
    }

    public function testCanAddInitializers()
    {
        $binding = new Binding('stdClass');
        $this->assertNull($binding->getInitializers());

        $init1 = function () {
        };
        $binding->initialize($init1);
        $this->assertEquals([$init1], $binding->getInitializers());

        $init2 = function () {
        };
        $binding->initialize($init2);
        $this->assertEquals([$init1, $init2], $binding->getInitializers());
    }
}
