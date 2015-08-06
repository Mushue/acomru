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

use KoolKode\Context\Bind\Binding;
use KoolKode\Context\Bind\ContainerBuilder;
use KoolKode\Context\Bind\SetterInjection;
use KoolKode\Context\Bind\TestMarker;
use KoolKode\Context\Scope\Singleton;

/**
 * @covers \KoolKode\Context\CompiledContainer
 * @covers \KoolKode\Context\AbstractContainer
 * @covers \KoolKode\Context\ContainerCompiler
 * @covers \KoolKode\Context\CompiledCodeFragment
 * @covers \KoolKode\Context\Bind\Binding
 * @covers \KoolKode\Context\Scope\SingletonScopeManager
 */
class CompiledContainerTest extends BaseContainerTest
{
    private static $typeCounter = 1;

    public function testCanCompileMultipleContainers()
    {
        $builder = new ContainerBuilder();
        $builder->bind('stdClass')->to(function () {
            $object = new \stdClass();
            $object->message = 'Hello';

            return $object;
        });

        $container1 = $this->compileContainer($builder);
        $container2 = $this->compileContainer($builder);

        $this->assertTrue($container1 instanceof CompiledContainer);
        $this->assertTrue($container2 instanceof CompiledContainer);

        $a = $container1->get('stdClass');
        $b = $container2->get('stdClass');

        $this->assertEquals($a, $b);
        $this->assertNotSame($a, $b);
    }

    protected function compileContainer(ContainerBuilder $builder, $dumpCode = false)
    {
        $typeName = 'KoolKode\Context\Compiled\Container' . self::$typeCounter++;

        $compiler = new ContainerCompiler($typeName);
        $code = $compiler->compile($builder);

        if ($dumpCode) {
            echo "\n\n", $code, "\n\n";
        }

        eval('?>' . $code);

        return new $typeName($builder->getParameters(), $builder->getInitializers());
    }

    public function testInitializeWillInjectContainer()
    {
        $container = new CompiledContainerMock();
        $object = new ContainerAwareMock();

        $this->assertNull($object->container);
        $this->assertNull($object->message);
        $container->initialize($object);
        $this->assertSame($container, $object->container);
        $this->assertNull($object->message);
    }

    public function testInitializeWillUseContainerInitializers()
    {
        $container = new CompiledContainerMock([], [new TestContainerInitializer()]);

        $object = new ContainerAwareMock();
        $this->assertNull($object->container);
        $this->assertNull($object->message);
        $container->initialize($object);
        $this->assertSame($container, $object->container);
        $this->assertSame('DONE', $object->message);
    }

    /**
     * @expectedException \KoolKode\Context\TypeNotFoundException
     */
    public function testCompilerThrowsExceptionWhenFactoryParamResolverIsNotPresent()
    {
        $builder = new ContainerBuilder();
        $builder->bind(TestMarker::class)
            ->to(function ($name) {
                return new TestMarker($name);
            });

        $this->prepareContainer($builder)->get('KoolKode\Context\TestMarker');
    }

    protected function prepareContainer(ContainerBuilder $builder, $dumpCode = false)
    {
        return $this->compileContainer($builder, $dumpCode);
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

    public function testWillLookupNonDelegateBinding()
    {
        $builder = new ContainerBuilder();
        $builder->bind('stdClass')->scoped(new Singleton());

        $container = $this->prepareContainer($builder);
        $std = $container->get('stdClass');

        $this->assertSame($std, $container->getBound(new Binding('stdClass')));
    }

    public function createFoo0()
    {
        return $this->createFoo();
    }

    protected function createFoo()
    {
        $std = new \stdClass();
        $std->args = func_get_args();

        return $std;
    }

    public function createFoo1(\stdClass $s1)
    {
        return $this->createFoo($s1);
    }

    public function createFoo2(\stdClass $s1, \stdClass $s2)
    {
        return $this->createFoo($s1, $s2);
    }

    public function createFoo3(\stdClass $s1, \stdClass $s2, \stdClass $s3)
    {
        return $this->createFoo($s1, $s2, $s3);
    }

    public function createFoo4(\stdClass $s1, \stdClass $s2, \stdClass $s3, \stdClass $s4)
    {
        return $this->createFoo($s1, $s2, $s3, $s4);
    }

    public function createFoo5(\stdClass $s1, \stdClass $s2, \stdClass $s3, \stdClass $s4, \stdClass $s5)
    {
        return $this->createFoo($s1, $s2, $s3, $s4, $s5);
    }

    public function provideFactoryAliasNum()
    {
        return [
            [0],
            [1],
            [2],
            [3],
            [4],
            [5]
        ];
    }

    /**
     * @dataProvider provideFactoryAliasNum
     */
    public function testFactoryMethodCalls($num)
    {
        $builder = new ContainerBuilder();
        $builder->bind('stdClass')->scoped(new Singleton());
        $builder->bind('foo')->to(__CLASS__, 'createFoo' . $num);

        $container = $this->prepareContainer($builder);
        $container->bindInstance(__CLASS__, $this);

        $arg = $container->get('stdClass');
        $std = $container->get('foo');
        $this->assertTrue($std instanceof \stdClass);
        $this->assertCount($num, $std->args);

        for ($i = 1; $i <= $num; $i++) {
            $this->assertSame($arg, $std->args[$i - 1]);
        }
    }

    public function testWillExpandClassConstantLookup()
    {
        $builder = new ContainerBuilder();

        $builder->bind(ScalarConstructorDummy::class)->to(function () {
            return new ScalarConstructorDummy(ScalarConstructorDummy::DUMMY_OPT1, 'N/A');
        });

        $container = $this->prepareContainer($builder);

        $dummy = $container->get(ScalarConstructorDummy::class);
        $this->assertTrue($dummy instanceof ScalarConstructorDummy);
        $this->assertEquals(ScalarConstructorDummy::DUMMY_OPT1, $dummy->value);
    }
}
