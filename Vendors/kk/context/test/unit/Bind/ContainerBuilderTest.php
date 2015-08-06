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

use KoolKode\Context\Container;
use KoolKode\Context\Scope\ApplicationScoped;
use KoolKode\Context\Scope\Dependent;
use KoolKode\Context\Scope\Singleton;

/**
 * @covers \KoolKode\Context\Bind\ContainerBuilder
 */
class ContainerBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function testCanCreateWithoutInitializers()
    {
        $builder = new ContainerBuilder();

        $this->assertEquals([], $builder->getInitializers());
    }

    public function testCanPopulateInitializers()
    {
        $init = $this->getMock('KoolKode\Context\Bind\ContainerInitializerInterface');

        $initializers = new ContainerInitializerLoader();
        $initializers->registerInitializer($init);

        $builder = new ContainerBuilder($initializers);

        $this->assertCount(1, $builder->getInitializers());
        $this->assertSame($init, $builder->getInitializers()[0]);
    }

    public function testCanDealWithContainerParams()
    {
        $builder = new ContainerBuilder();

        $this->assertEquals([], $builder->getParameters());
        $this->assertFalse($builder->hasParameter('foo'));

        $this->assertSame($builder, $builder->setParameter('foo', 'bar'));
        $builder->setParameter('baz', 'bum');

        $this->assertEquals(['foo' => 'bar', 'baz' => 'bum'], $builder->getParameters());
        $this->assertTrue($builder->hasParameter('foo'));
        $this->assertEquals('bar', $builder->getParameter('foo'));
        $this->assertFalse($builder->hasParameter('bar'));

        $this->assertSame($builder, $builder->removeParameter('foo'));
        $this->assertEquals(['baz' => 'bum'], $builder->getParameters());
        $this->assertFalse($builder->hasParameter('foo'));
        $this->assertEquals('world', $builder->getParameter('subject', 'world'));
    }

    /**
     * @expectedException \KoolKode\Context\ContextParamNotFoundException
     */
    public function testThrowsExceptionWhenParameterIsNotSetAndNoDefaultValueGiven()
    {
        (new ContainerBuilder())->getParameter('foo');
    }

    public function testCanAddBindings()
    {
        $builder = new ContainerBuilder();

        $this->assertEquals([], $builder->getBindings());

        $b1 = $builder->bind('foo');
        $b2 = $builder->bind('bar');

        $this->assertTrue($builder->isBound('foo'));
        $this->assertTrue($builder->isBound('bar'));
        $this->assertFalse($builder->isBound('baz'));

        $this->assertCount(2, $builder->getBindings());
        $this->assertContains($b1, $builder->getBindings());
        $this->assertContains($b2, $builder->getBindings());
    }

    public function testCanReOpenBindingForFurtherConfiguration()
    {
        $builder = new ContainerBuilder();
        $b1 = $builder->bind('foo')->scoped(new Singleton());
        $b2 = $builder->bind('foo');

        $this->assertSame($b1, $b2);
    }

    public function testDetectsBindingsThatRequireScopedProxies()
    {
        $builder = new ContainerBuilder();
        $this->assertFalse($builder->isScopedProxyRequired('foo'));

        $builder->bind('bar')->scoped(new Singleton());
        $this->assertFalse($builder->isScopedProxyRequired('fOO'));
        $this->assertFalse($builder->isScopedProxyRequired('bar'));
        $this->assertFalse($builder->isScopedProxyRequired('bum'));

        $builder->bind('baz')->scoped(new ApplicationScoped());
        $this->assertFalse($builder->isScopedProxyRequired('foo'));
        $this->assertFalse($builder->isScopedProxyRequired('bAr'));
        $this->assertTrue($builder->isScopedProxyRequired('baz'));
        $this->assertFalse($builder->isScopedProxyRequired('BUM'));

        $builder->bind('bum')->scoped(new Dependent());
        $this->assertFalse($builder->isScopedProxyRequired('foo'));
        $this->assertFalse($builder->isScopedProxyRequired('bAr'));
        $this->assertTrue($builder->isScopedProxyRequired('baz'));
        $this->assertFalse($builder->isScopedProxyRequired('BUM'));
    }

    public function testPassesSettingsToContainerDuringBuild()
    {
        $initializer = $this->getMock('KoolKode\Context\Bind\ContainerInitializerInterface');

        $init = new ContainerInitializerLoader();
        $init->registerInitializer($initializer);

        $builder = new ContainerBuilder($init);
        $builder->setParameter('foo', 'FOO');
        $builder->setParameter('bar', 'BAR');

        $b1 = $builder->bind('foo')
            ->scoped(new Singleton())
            ->marked(new TestMarker('My Foo'));

        $b2 = $builder->bind('bar')
            ->scoped(new ApplicationScoped());

        $container = $builder->build();

        $this->assertTrue($container instanceof Container);
        $this->assertEquals(['foo' => 'FOO', 'bar' => 'BAR'], $container->getParameters());

        $initializers = $container->getInitializers();
        $this->assertCount(1, $initializers);
        $this->assertContains($initializer, $initializers);

        $bindings = $container->getBindings();
        $this->assertCount(2, $bindings);

        $this->assertSame($b1, $bindings['foo']);
        $this->assertSame($b2, $bindings['bar']);
    }

    public function provideForbiddentTypeNames()
    {
        return [
            ['KoolKode\Config\Configuration'],
            ['KoolKode\Context\ContainerInterface'],
            ['KoolKode\Context\ExposedContainerInterface'],
            ['KoolKode\Context\Scope\ScopedContainerInterface']
        ];
    }

    /**
     * @dataProvider provideForbiddentTypeNames
     * @expectedException \InvalidArgumentException
     */
    public function testCannotBindForbiddenTypes($typeName)
    {
        (new ContainerBuilder())->bind($typeName);
    }
}
