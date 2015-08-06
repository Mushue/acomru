<?php

/*
 * This file is part of KoolKode Context and Dependency Injection.
*
* (c) Martin Schröder <m.schroeder2007@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace KoolKode\Context;

use KoolKode\Config\Configuration;
use KoolKode\Context\Bind\ContainerBuilder;
use KoolKode\Context\Bind\SetterInjection;
use KoolKode\Context\Bind\TestMarker;
use KoolKode\Context\Scope\ApplicationScopeManager;
use KoolKode\Context\Scope\ScopedProxyInterface;
use KoolKode\Context\Scope\ScopeManagerInterface;
use KoolKode\Context\Scope\Singleton;
use KoolKode\Context\Scope\TestScope;

/**
 * @covers \KoolKode\Context\Container
 * @covers \KoolKode\Context\AbstractContainer
 * @covers \KoolKode\Context\Bind\Binding
 * @covers \KoolKode\Context\Scope\SingletonScopeManager
 */
class ContainerTest extends BaseContainerTest
{
    /**
     * @expectedException \KoolKode\Context\ContextLookupException
     */
    public function testThrowsExceptionWhenFactoryParamResolverIsNotPresent()
    {
        $builder = new ContainerBuilder();
        $builder->bind('KoolKode\Context\TestMarker')
            ->to(function ($name) {
                return new TestMarker($name);
            });

        $this->prepareContainer($builder)->get('KoolKode\Context\TestMarker');
    }

    protected function prepareContainer(ContainerBuilder $builder)
    {
        return $builder->build();
    }

    public function testCanLoadBindingsFromContainer()
    {
        $builder = new ContainerBuilder();
        $builder->bind('stdClass')->scoped(new Singleton());

        $container = $this->prepareContainer($builder);
        $this->assertEquals($builder->getBindings(), array_change_key_case($container->getBindings(), CASE_LOWER));
    }

    public function testSetterInjectionFailsWhenNoParamIsPresent()
    {
        $builder = new ContainerBuilder();
        $builder->bind('KoolKode\Context\SetterInjectionFail1')
            ->marked(new SetterInjection(SetterInjection::CONVENTION));

        $object = $this->prepareContainer($builder)->get('KoolKode\Context\SetterInjectionFail1');
        $this->assertTrue($object instanceof SetterInjectionFail1);
        $this->assertFalse($object->done);
    }

    public function testSetterInjectionFailsWhenParamDeclaresNoTypeHint()
    {
        $builder = new ContainerBuilder();
        $builder->bind('KoolKode\Context\SetterInjectionFail2')
            ->marked(new SetterInjection(SetterInjection::CONVENTION));

        $object = $this->prepareContainer($builder)->get('KoolKode\Context\SetterInjectionFail2');
        $this->assertTrue($object instanceof SetterInjectionFail2);
        $this->assertFalse($object->done);
    }

    public function testCanCreateInstanceOfInstanceBinding()
    {
        $builder = new ContainerBuilder();
        $binding = $builder->bind('foo')->to('stdClass');

        $container = $this->prepareContainer($builder);
        $std = $container->createInstance($binding);
        $this->assertTrue($std instanceof \stdClass);
    }

    public function testCanCreateInstanceOfAliasBinding()
    {
        $builder = new ContainerBuilder();
        $binding = $builder->bind('foo')->toAlias('stdClass');

        $container = $this->prepareContainer($builder);
        $std = $container->createInstance($binding);
        $this->assertTrue($std instanceof \stdClass);
    }

    public function testCanCreateInstanceUsingFactory()
    {
        $builder = new ContainerBuilder();
        $binding = $builder->bind('foo')->to(function () {
            return new \stdClass();
        });

        $container = $this->prepareContainer($builder);
        $std = $container->createInstance($binding);
        $this->assertTrue($std instanceof \stdClass);
    }

    public function testCanGetApplicationScope()
    {
        $container = $this->prepareContainer(new ContainerBuilder());
        $app = $container->registerScope(new ApplicationScopeManager());

        $this->assertSame($app, $container->getApplicationScope());
    }

    /**
     * @expectedException \KoolKode\Context\Scope\ScopeNotFoundException
     */
    public function testThrowsExceptionWhenApplicationScopeIsNotRegistered()
    {
        $this->prepareContainer(new ContainerBuilder())->getApplicationScope();
    }
}
