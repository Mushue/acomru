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
use KoolKode\Context\Bind\BindingInterface;
use KoolKode\Context\Bind\ContainerBuilder;
use KoolKode\Context\Bind\ContainerInitializerLoader;
use KoolKode\Context\Bind\SetterBlacklist;
use KoolKode\Context\Bind\SetterInjection;
use KoolKode\Context\Bind\SetterWhitelist;
use KoolKode\Context\Bind\TestMarker;
use KoolKode\Context\Scope\ApplicationScoped;
use KoolKode\Context\Scope\ApplicationScopeManager;
use KoolKode\Context\Scope\Dependent;
use KoolKode\Context\Scope\ScopedProxyInterface;
use KoolKode\Context\Scope\Singleton;
use KoolKode\Context\Scope\ScopeManagerInterface;
use KoolKode\Context\Scope\ScopeNotFoundException;

use KoolKode\Config as Conf;

abstract class BaseContainerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \RuntimeException
     */
    public function testCannotSerializeContainer()
    {
        serialize($this->prepareContainer(new ContainerBuilder()));
    }

    protected abstract function prepareContainer(ContainerBuilder $builder);

    public function testCanSetAndRetrieveConfig()
    {
        $container = $this->prepareContainer(new ContainerBuilder());
        $config = new Configuration();

        $this->assertNotSame($config, $container->getConfiguration());
        $container->setConfiguration($config);
        $this->assertSame($config, $container->getConfiguration());
    }

    public function testCanPassAndAccessContainerParams()
    {
        $builder = new ContainerBuilder();
        $builder->setParameter('foo', 'FOO');
        $builder->setParameter('bar', 'BAR');

        $container = $this->prepareContainer($builder);

        $this->assertEquals($builder->getParameters(), $container->getParameters());
        $this->assertTrue($container->hasParameter('bar'));
        $this->assertFalse($container->hasParameter('baz'));
        $this->assertEquals('FOO', $container->getParameter('foo'));
        $this->assertEquals('BUM', $container->getParameter('bum', 'BUM'));
    }

    /**
     * @expectedException \KoolKode\Context\ContextParamNotFoundException
     */
    public function testThrowsExceptionWhenParamIsNotSetAndNoDefaultValueGiven()
    {
        $this->prepareContainer(new ContainerBuilder())->getParameter('foo');
    }

    public function testCanBindInstance()
    {
        $container = $this->prepareContainer(new ContainerBuilder());

        $this->assertFalse($container->hasProxy('test'));
        $this->assertFalse($container->hasProxy('foo'));

        $container->bindInstance('test', $this);
        $this->assertTrue($container->hasProxy('test'));
        $this->assertFalse($container->hasProxy('foo'));
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
        $this->prepareContainer(new ContainerBuilder())->bindInstance($typeName, new \stdClass());
    }

    public function testCanGetBinding()
    {
        $builder = new ContainerBuilder();
        $builder->bind('stdClass')->scoped(new ApplicationScoped());

        $container = $this->prepareContainer($builder);
        $binding = $container->getBinding('stdClass');

        $this->assertEquals('stdClass', $binding->getTypeName());
        $this->assertEquals(ApplicationScoped::class, $binding->getScope());

        // Verify bindings are cached properly.
        $this->assertSame($binding, $container->getBinding('stdClass'));
        $this->assertSame($binding, $container->getBinding('stdClass'));
    }

    /**
     * @expectedException \KoolKode\Context\TypeNotBoundException
     */
    public function testThrowsExceptionWhenAttemptingToGetNonExistantBinding()
    {
        $this->prepareContainer(new ContainerBuilder())->getBinding('stdClass');
    }

    public function testCanCheckProxyTypeAndBoundName()
    {
        $container = $this->prepareContainer(new ContainerBuilder());
        $std = new \stdClass();

        $this->assertFalse($container->isProxy($std));
        $this->assertNull($container->getBoundTypeOfProxy($std));

        $container->bindInstance('stdClass', $std);
        $this->assertTrue($container->isProxy($std));
        $this->assertEquals('stdClass', $container->getBoundTypeOfProxy($std));
    }

    public function testCanPopulateEmptyArguments()
    {
        $container = $this->prepareContainer(new ContainerBuilder());
        $args = $container->populateArguments(new \ReflectionMethod(__CLASS__, __FUNCTION__));

        $this->assertEquals([], $args);
    }

    public function someScalarArgsMethod($arg1, $arg2)
    {
    }

    /**
     * @expectedException \KoolKode\Context\ContextLookupException
     */
    public function testCannotPopulateArgumentsOfScalarCallback()
    {
        $this->prepareContainer(new ContainerBuilder())->populateArguments(new \ReflectionMethod(__CLASS__, 'someScalarArgsMethod'));
    }

    public function testPopulatesArgumentsWithTypeHintUsingContainer()
    {
        $container = $this->prepareContainer(new ContainerBuilder());
        $callback = function (ContainerInterface $container) {
        };
        $args = $container->populateArguments(new \ReflectionFunction($callback));

        $this->assertCount(1, $args);
        $this->assertSame($container, $args[0]);
    }

    /**
     * @expectedException \KoolKode\Context\ContextLookupException
     */
    public function testPopulateArgumentsThrowsExceptionWhenNoTypeHintAndNoDefaultValuesIsGiven()
    {
        $container = $this->prepareContainer(new ContainerBuilder());
        $callback = function (ContainerInterface $a, $b, $c = false) {
        };

        $container->populateArguments(new \ReflectionFunction($callback));
    }

    public function testPopulateArgumentsUsesDefaultValueOnMissingBoundType()
    {
        $container = $this->prepareContainer(new ContainerBuilder());
        $callback = function (ContainerInterface $container, \Countable $counter = NULL) {
        };

        $args = $container->populateArguments(new \ReflectionFunction($callback));
        $this->assertCount(2, $args);
        $this->assertSame($container, $args[0]);
        $this->assertNull($args[1]);
    }

    public function testPopulateArgumentsUsesDefaultValuesForArgsWithoutTypeHints()
    {
        $container = $this->prepareContainer(new ContainerBuilder());
        $callback = function ($a = 1, array $b = [], $foo = 'bar') {
        };

        $args = $container->populateArguments(new \ReflectionFunction($callback));
        $this->assertEquals([1, [], 'bar'], $args);
    }

    public function testPopulateArgumentsWillSkipArgumentsAsSpecified()
    {
        $container = $this->prepareContainer(new ContainerBuilder());
        $callback = function ($foo, $bar = NULL, $baz = 'baz', ContainerInterface $container = NULL) {
        };

        $args = $container->populateArguments(new \ReflectionFunction($callback), 2);
        $this->assertCount(2, $args);
        $this->assertEquals('baz', $args[0]);
        $this->assertSame($container, $args[1]);
    }

    /**
     * @expectedException \KoolKode\Context\ContextLookupException
     */
    public function testPopulateArgumentsThrowsExceptionWhenRequiredBoundObjectCannotBeCreated()
    {
        $container = $this->prepareContainer(new ContainerBuilder());
        $callback = function (ContainerInterface $a, AbstractContainer $b) {
        };

        $container->populateArguments(new \ReflectionFunction($callback));
    }

    public function provideAbstractTypeNames()
    {
        return [
            ['KoolKode\Context\ContainerAwareInterface'],
            ['KoolKode\Context\Scope\ScopedProxyTrait'],
            ['KoolKode\Context\AbstractContainer']
        ];
    }

    public function testCanCreateObjectWithStandardConstructor()
    {
        $container = $this->prepareContainer(new ContainerBuilder());
        $object = $container->createObject('KoolKode\Context\StandardConstructor');

        $this->assertTrue($object instanceof StandardConstructor);
        $this->assertTrue($object->foo instanceof \stdClass);
    }

    public function testCanCreateObjectWithConstructorArgs3()
    {
        $container = $this->prepareContainer(new ContainerBuilder());
        $std = $container->bindInstance('stdClass', new \stdClass());

        $object = $container->createObject('KoolKode\Context\ConstructorArgs3');

        $this->assertTrue($object instanceof ConstructorArgs3);
        $this->assertSame($std, $object->a1);
        $this->assertSame($std, $object->a2);
        $this->assertSame($std, $object->a3);
    }

    public function testCanCreateObjectWithConstructorArgs4()
    {
        $container = $this->prepareContainer(new ContainerBuilder());
        $std = $container->bindInstance('stdClass', new \stdClass());

        $object = $container->createObject('KoolKode\Context\ConstructorArgs4');

        $this->assertTrue($object instanceof ConstructorArgs4);
        $this->assertSame($std, $object->a1);
        $this->assertSame($std, $object->a2);
        $this->assertSame($std, $object->a3);
        $this->assertSame($std, $object->a4);
    }

    /**
     * @dataProvider provideAbstractTypeNames
     * @expectedException \KoolKode\Context\ContextLookupException
     */
    public function testCannotCreateInstanceOfAbstractType($typeName)
    {
        $this->prepareContainer(new ContainerBuilder())->createObject($typeName);
    }

    /**
     * @expectedException \KoolKode\Context\TypeNotFoundException
     */
    public function testCannotCreateInstanceOfMissingType()
    {
        $this->prepareContainer(new ContainerBuilder())->createObject('MyFooIsBar');
    }

    public function testCanCreateNewInstanceWithoutConstructor()
    {
        $container = $this->prepareContainer(new ContainerBuilder());

        $object = $container->createObject('stdClass');
        $this->assertTrue($object instanceof \stdClass);

        $object = $container->get('stdClass');
        $this->assertTrue($object instanceof \stdClass);
    }

    public function testCanPassConstructorArgumentsWhenCreatingInstance()
    {
        $container = $this->prepareContainer(new ContainerBuilder());
        $factory = new TestFactory();

        $container->bindInstance('KoolKode\Context\TestFactory', $factory);

        $object = $container->createObject('KoolKode\Context\ConstructorInjectionDummy1');
        $this->assertTrue($object instanceof ConstructorInjectionDummy1);
        $this->assertSame($container, $object->container);
        $this->assertSame($factory, $object->factory);

        $object = $container->get('KoolKode\Context\ConstructorInjectionDummy1');
        $this->assertTrue($object instanceof ConstructorInjectionDummy1);
        $this->assertSame($container, $object->container);
        $this->assertSame($factory, $object->factory);
    }

    public function testCanPassConstructorArgumentsUsingReflection()
    {
        $container = $this->prepareContainer(new ContainerBuilder());

        $object = $container->createObject('KoolKode\Context\ConstructorInjectionDummy2');
        $this->assertTrue($object instanceof ConstructorInjectionDummy2);
        $this->assertCount(7, $object->containers);

        for ($i = 0; $i < 7; $i++) {
            $this->assertSame($container, $object->containers[$i]);
        }

        $object = $container->get('KoolKode\Context\ConstructorInjectionDummy2');
        $this->assertTrue($object instanceof ConstructorInjectionDummy2);
        $this->assertCount(7, $object->containers);

        for ($i = 0; $i < 7; $i++) {
            $this->assertSame($container, $object->containers[$i]);
        }
    }

    public function testCreateObjectWillPassContainerToAwareSubject()
    {
        $container = $this->prepareContainer(new ContainerBuilder());

        $object = $container->createObject('KoolKode\Context\ContainerAwareMock');
        $this->assertTrue($object instanceof ContainerAwareMock);
        $this->assertSame($container, $object->container);
        $this->assertNull($object->message);

        $object = $container->get('KoolKode\Context\ContainerAwareMock');
        $this->assertTrue($object instanceof ContainerAwareMock);
        $this->assertSame($container, $object->container);
        $this->assertNull($object->message);
    }

    public function testCreateObjectUtilizesContainerInitializers()
    {
        $test = new TestContainerInitializer();
        $init = new ContainerInitializerLoader();
        $init->registerInitializer($test);

        $container = $this->prepareContainer(new ContainerBuilder($init));
        $this->assertCount(1, $container->getInitializers());
        $this->assertSame($test, $container->getInitializers()[0]);

        $object = $container->createObject('KoolKode\Context\ContainerAwareMock');
        $this->assertTrue($object instanceof ContainerAwareMock);
        $this->assertSame($container, $object->container);
        $this->assertSame('DONE', $object->message);

        $object = $container->get('KoolKode\Context\ContainerAwareMock');
        $this->assertTrue($object instanceof ContainerAwareMock);
        $this->assertSame($container, $object->container);
        $this->assertSame('DONE', $object->message);
    }

    /**
     * @expectedException \KoolKode\Context\Scope\ScopedProxyException
     */
    public function testCannotGenerateScopedProxyForTrait()
    {
        $this->prepareContainer(new ContainerBuilder())->loadScopedProxy('KoolKode\Context\Scope\ScopedProxyTrait');
    }

    public function testWillReUseScopedProxyClass()
    {
        $typeName = 'KoolKode\Context\CreationTester';
        $proxyName = $typeName . '__scoped';

        $container = $this->prepareContainer(new ContainerBuilder());

        $proxy = $container->loadScopedProxy($typeName);
        $ref = new \ReflectionClass($proxy);
        $this->assertEquals($proxyName, $ref->name);
        $this->assertTrue($ref->implementsInterface('KoolKode\Context\Scope\ScopedProxyInterface'));

        $proxy = $container->loadScopedProxy($typeName);
        $ref = new \ReflectionClass($proxy);
        $this->assertEquals($proxyName, $ref->name);
        $this->assertTrue($ref->implementsInterface('KoolKode\Context\Scope\ScopedProxyInterface'));
    }

    public function provideFactoriesWithArguments()
    {
        return [
            [0, function () {
                return new ScalarConstructorDummy(func_get_args());
            }], [1, function (\stdClass $s1) {
                return new ScalarConstructorDummy(func_get_args());
            }], [2, function (\stdClass $s1, \stdClass $s2) {
                return new ScalarConstructorDummy(func_get_args());
            }], [3, function (\stdClass $s1, \stdClass $s2, \stdClass $s3) {
                return new ScalarConstructorDummy(func_get_args());
            }], [4, function (\stdClass $s1, \stdClass $s2, \stdClass $s3, \stdClass $s4) {
                return new ScalarConstructorDummy(func_get_args());
            }], [5, function (\stdClass $s1, \stdClass $s2, \stdClass $s3, \stdClass $s4, \stdClass $s5) {
                return new ScalarConstructorDummy(func_get_args());
            }]
        ];
    }

    /**
     * @dataProvider provideFactoriesWithArguments
     */
    public function testWillPopulateFactoryArguments($num, \Closure $factory)
    {
        $builder = new ContainerBuilder();
        $builder->bind('KoolKode\Context\ScalarConstructorDummy')->to($factory);

        $container = $this->prepareContainer($builder);
        $std = $container->bindInstance('stdClass', new \stdClass());

        $object = $container->get('KoolKode\Context\ScalarConstructorDummy');
        $this->assertTrue($object instanceof ScalarConstructorDummy);
        $this->assertTrue(is_array($object->value));
        $this->assertCount($num, $object->value);

        for ($i = 0; $i < $num; $i++) {
            $this->assertSame($std, $object->value[$i]);
        }
    }

    public function testFactoryWillNotActivateScopedProxy()
    {
        $builder = new ContainerBuilder();
        $builder->bind('KoolKode\Context\ContainerAwareMock')
            ->scoped(new ApplicationScoped())
            ->to(function () {
                return new ContainerAwareMock();
            });

        $container = $this->prepareContainer($builder);
        $container->registerScope(new ApplicationScopeManager());
        $mock = $container->get('KoolKode\Context\ContainerAwareMock');

        $this->assertTrue($mock instanceof ContainerAwareMock);
        $this->assertTrue($mock instanceof ScopedProxyInterface);
        $this->assertFalse($mock->K2IsProxyBound());

        $mock->setMessage('foo');
        $this->assertTrue($mock->K2IsProxyBound());
        $this->assertSame($container, $mock->container);
        $this->assertEquals('foo', $mock->message);
    }

    public function testDoesNotActivateScopedProxyReturnedByFactory()
    {
        $builder = new ContainerBuilder();

        $builder->bind('stdClass')->to(function (ContainerAwareMock $mock) {
            return $mock;
        });
        $builder->bind('KoolKode\Context\ContainerAwareMock')->scoped(new ApplicationScoped());

        $container = $this->prepareContainer($builder);
        $container->registerScope(new ApplicationScopeManager());

        $mock = $container->get('stdClass');
        $this->assertTrue($mock instanceof ContainerAwareMock);
        $this->assertTrue($mock instanceof ScopedProxyInterface);
        $this->assertFalse($mock->K2IsProxyBound());
    }

    public function testWillPassNonClosureResolverArguments()
    {
        $message = '...in a bottle';

        $builder = new ContainerBuilder();
        $builder->bind('KoolKode\Context\CreationTester')
            ->resolve('message', $message);

        $container = $this->prepareContainer($builder);

        $object = $container->createObject('KoolKode\Context\CreationTester', ['message' => $message]);
        $this->assertTrue($object instanceof CreationTester);
        $this->assertEquals($object->message, $message);

        $object = $container->get('KoolKode\Context\CreationTester');
        $this->assertTrue($object instanceof CreationTester);
        $this->assertEquals($object->message, $message);
    }

    public function testWillApplyClosureResolverAndInitializerDuringObjectCreation()
    {
        $resolver = function (\stdClass $obj) {
            return 'Hello ' . $obj->subject . '!';
        };

        $init = function (CreationTester $tester, \stdClass $obj) {
            $tester->foo = $obj->foo;
        };

        $builder = new ContainerBuilder();
        $builder->bind('KoolKode\Context\CreationTester')
            ->resolve('message', $resolver)
            ->initialize($init);

        $dummy = new \stdClass();
        $dummy->subject = 'World';
        $dummy->foo = 'bar';

        $container = $this->prepareContainer($builder);
        $container->bindInstance('stdClass', $dummy);

        $object = $container->createObject('KoolKode\Context\CreationTester', ['message' => $resolver], [$init]);
        $this->assertTrue($object instanceof CreationTester);
        $this->assertSame($object->container, $container);
        $this->assertEquals('Hello World!', $object->message);
        $this->assertTrue(property_exists($object, 'foo'));
        $this->assertEquals($dummy->foo, $object->foo);

        $object = $container->get('KoolKode\Context\CreationTester');
        $this->assertTrue($object instanceof CreationTester);
        $this->assertSame($object->container, $container);
        $this->assertEquals('Hello World!', $object->message);
        $this->assertTrue(property_exists($object, 'foo'));
        $this->assertEquals($dummy->foo, $object->foo);
    }

    public function provideResolversWithParams()
    {
        return [
            [0, function () {
                return func_get_args();
            }], [1, function (\stdClass $s1) {
                return func_get_args();
            }], [2, function (\stdClass $s1, \stdClass $s2) {
                return func_get_args();
            }], [3, function (\stdClass $s1, \stdClass $s2, \stdClass $s3) {
                return func_get_args();
            }], [4, function (\stdClass $s1, \stdClass $s2, \stdClass $s3, \stdClass $s4) {
                return func_get_args();
            }], [5, function (\stdClass $s1, \stdClass $s2, \stdClass $s3, \stdClass $s4, \stdClass $s5) {
                return func_get_args();
            }]
        ];
    }

    /**
     * @dataProvider provideResolversWithParams
     */
    public function testResolversWithParams($num, \Closure $resolver)
    {
        $builder = new ContainerBuilder();
        $builder->bind('KoolKode\Context\ScalarConstructorDummy')
            ->resolve('value', $resolver);

        $container = $this->prepareContainer($builder);
        $std = $container->bindInstance('stdClass', new \stdClass());

        $object = $container->get('KoolKode\Context\ScalarConstructorDummy');
        $this->assertTrue($object instanceof ScalarConstructorDummy);
        $this->assertTrue(is_array($object->value));
        $this->assertCount($num, $object->value);

        for ($i = 0; $i < $num; $i++) {
            $this->assertSame($std, $object->value[$i]);
        }
    }

    /**
     * @expectedException \KoolKode\Context\ContextLookupException
     */
    public function testObjectCreationWillFailIfResolverDependencyIsNotAvailable()
    {
        $container = $this->prepareContainer(new ContainerBuilder());
        $resolver = function (\Serializable $foo) {
        };

        $container->createObject('KoolKode\Context\CreationTester', ['message' => $resolver]);
    }

    /**
     * @expectedException \KoolKode\Context\ContextLookupException
     */
    public function testContextLookupWillFailIfResolverDependencyIsNotAvailable()
    {
        $builder = new ContainerBuilder();
        $builder->bind('KoolKode\Context\CreationTester')
            ->resolve('message', function (\Serializable $leFou) {
            });

        $this->prepareContainer($builder)->get('KoolKode\Context\CreationTester');
    }

    /**
     * @expectedException \KoolKode\Context\ContextLookupException
     */
    public function testObjectCreationWillFailWhenInitializerDependencyIsNotAvailable()
    {
        $container = $this->prepareContainer(new ContainerBuilder());
        $init = function ($obj, \Serializable $foo) {
        };

        $container->createObject('stdClass', NULL, [$init]);
    }

    /**
     * @expectedException \KoolKode\Context\ContextLookupException
     */
    public function testContextLookupWillFailWhenInitializerDependencyIsNotAvailable()
    {
        $builder = new ContainerBuilder();
        $builder->bind('stdClass')
            ->initialize(function ($obj, \Serializable $leFou) {
            });

        $this->prepareContainer($builder)->get('stdClass');
    }

    public function provideBindingInitializersWithParams()
    {
        return [
            [1, function ($object, \stdClass $s1) {
                $object->set1($s1);
            }], [2, function ($object, \stdClass $s1, \stdClass $s2) {
                $object->set2($s1, $s2);
            }], [3, function ($object, \stdClass $s1, \stdClass $s2, \stdClass $s3) {
                $object->set3($s1, $s2, $s3);
            }], [4, function ($object, \stdClass $s1, \stdClass $s2, \stdClass $s3, \stdClass $s4) {
                $object->set4($s1, $s2, $s3, $s4);
            }], [5, function ($object, \stdClass $s1, \stdClass $s2, \stdClass $s3, \stdClass $s4, \stdClass $s5) {
                $object->set5($s1, $s2, $s3, $s4, $s5);
            }]
        ];
    }

    /**
     * @dataProvider provideBindingInitializersWithParams
     */
    public function testBindingInitializersWithParams($num, \Closure $initializer)
    {
        $builder = new ContainerBuilder();
        $builder->bind('KoolKode\Context\SetterInjectionExample2')
            ->initialize($initializer);

        $container = $this->prepareContainer($builder);
        $std = $container->bindInstance('stdClass', new \stdClass());

        $object = $container->get('KoolKode\Context\SetterInjectionExample2');
        $this->assertTrue($object instanceof SetterInjectionExample2);

        for ($i = 1; $i <= $num; $i++) {
            $this->assertSame($std, $object->{'s' . $i});
        }

        for (; $i <= 5; $i++) {
            $this->assertNull($object->{'s' . $i});
        }
    }

    public function testPopulateArgumentsWillInjectScopedConfigUsingInjectionPoint()
    {
        $ref = new \ReflectionClass(__CLASS__);

        $data = [
            'count' => 1337,
            'foo' => 'bar'
        ];

        $config = new Configuration([
            'kk' => [
                'context' => [
                    strtolower($ref->getShortName()) => $data
                ]
            ]
        ]);

        $container = $this->prepareContainer(new ContainerBuilder());
        $container->setConfiguration($config);

        $method = new \ReflectionMethod(__CLASS__, 'showMeTheConfig');

        $args = $container->populateArguments($method, 0, NULL, new InjectionPoint(__CLASS__, $method->name));
        $this->assertCount(1, $args);
        $this->assertTrue($args[0] instanceof Configuration);
        $this->assertEquals($data, $args[0]->toArray());
    }

    public function testPopulateArgumentsWillInjectEmptyConfigWhenNoConfigFound()
    {
        $container = $this->prepareContainer(new ContainerBuilder());

        $args = $container->populateArguments(new \ReflectionMethod(__CLASS__, 'showMeTheConfig'));
        $this->assertCount(1, $args);
        $this->assertTrue($args[0] instanceof Configuration);
        $this->assertEquals([], $args[0]->toArray());
    }

    public function showMeTheConfig(Configuration $config)
    {
    }

    public function testSetterInjectionUsingReflection()
    {
        $test = new \stdClass();
        $container = $this->prepareContainer(new ContainerBuilder());
        $container->bindInstance('stdClass', $test);

        $object = $container->createObject('KoolKode\Context\SetterInjectionExample');
        $this->assertTrue($object instanceof SetterInjectionExample);
        $this->assertNull($object->foo);
        $this->assertNull($object->bar);
        $this->assertNull($object->baz);

        $object = $container->createObject('KoolKode\Context\SetterInjectionExample', NULL, NULL, [new SetterInjection(SetterInjection::CONVENTION)]);
        $this->assertTrue($object instanceof SetterInjectionExample);
        $this->assertSame($test, $object->foo);
        $this->assertNull($object->bar);
        $this->assertNull($object->baz);
    }

    public function provideSetterInjectionArgCounts()
    {
        return [
            [1],
            [2],
            [3],
            [4],
            [5]
        ];
    }

    /**
     * @dataProvider provideSetterInjectionArgCounts
     */
    public function testSetterInjectionWithMultipleArguments($num)
    {
        $std = new \stdClass();

        $builder = new ContainerBuilder();
        $builder->bind('KoolKode\Context\SetterInjectionExample2')
            ->marked(new SetterInjection(new SetterWhitelist('set' . $num)));

        $container = $this->prepareContainer($builder);
        $container->bindInstance('stdClass', $std);

        $object = $container->get('KoolKode\Context\SetterInjectionExample2');
        $this->assertTrue($object instanceof SetterInjectionExample2);

        for ($i = 1; $i <= $num; $i++) {
            $this->assertSame($std, $object->{'s' . $i});
        }

        for (; $i <= 5; $i++) {
            $this->assertNull($object->{'s' . $i});
        }
    }

    public function testCombinedSetterInjection()
    {
        $test = new \stdClass();

        $builder = new ContainerBuilder();
        $builder->bind('KoolKode\Context\SetterInjectionExample')
            ->marked(new SetterInjection(SetterInjection::CONVENTION))
            ->marked(new SetterInjection(new SetterWhitelist('Bar')));

        $container = $this->prepareContainer($builder);
        $container->bindInstance('stdClass', $test);

        $object = $container->get('KoolKode\Context\SetterInjectionExample');
        $this->assertTrue($object instanceof SetterInjectionExample);
        $this->assertSame($test, $object->foo);
        $this->assertSame($test, $object->bar);
        $this->assertNull($object->baz);
    }

    public function testSetterInjectionUsingConvention()
    {
        $test = new \stdClass();

        $builder = new ContainerBuilder();
        $builder->bind('KoolKode\Context\SetterInjectionExample')
            ->marked(new SetterInjection(SetterInjection::CONVENTION));

        $container = $this->prepareContainer($builder);
        $container->bindInstance('stdClass', $test);

        $object = $container->get('KoolKode\Context\SetterInjectionExample');
        $this->assertTrue($object instanceof SetterInjectionExample);
        $this->assertSame($test, $object->foo);
        $this->assertNull($object->bar);
        $this->assertNull($object->baz);
    }

    public function testSetterInjectionUsingUnspecifiedFilter()
    {
        $test = new \stdClass();

        $builder = new ContainerBuilder();
        $builder->bind('KoolKode\Context\SetterInjectionExample')
            ->marked(new SetterInjection());

        $container = $this->prepareContainer($builder);
        $container->bindInstance('stdClass', $test);

        $object = $container->get('KoolKode\Context\SetterInjectionExample');
        $this->assertSame($test, $object->foo);
        $this->assertSame($test, $object->bar);
        $this->assertNull($object->baz);
    }

    public function testSetterInjectionUsingBlacklistFilter()
    {
        $test = new \stdClass();

        $builder = new ContainerBuilder();
        $builder->bind('KoolKode\Context\SetterInjectionExample')
            ->marked(new SetterInjection(new SetterBlacklist('Foo')));

        $container = $this->prepareContainer($builder);
        $container->bindInstance('stdClass', $test);

        $object = $container->get('KoolKode\Context\SetterInjectionExample');
        $this->assertNull($object->foo);
        $this->assertSame($test, $object->bar);
        $this->assertNull($object->baz);
    }

    public function testSetterInjectionUsingWhitelistFilter()
    {
        $test = new \stdClass();

        $builder = new ContainerBuilder();
        $builder->bind('KoolKode\Context\SetterInjectionExample')
            ->marked(new SetterInjection(new SetterWhitelist('setFoo')));

        $container = $this->prepareContainer($builder);
        $container->bindInstance('stdClass', $test);

        $object = $container->get('KoolKode\Context\SetterInjectionExample');
        $this->assertSame($test, $object->foo);
        $this->assertNull($object->bar);
        $this->assertNull($object->baz);
    }

    public function testWillPerformSetterInjectionInInlineFactoryBinding()
    {
        $builder = new ContainerBuilder();
        $builder->bind('KoolKode\Context\SetterInjectionExample2')
            ->marked(new SetterInjection(new SetterWhitelist('set1')))
            ->to(function () {
                return new SetterInjectionExample2();
            });

        $container = $this->prepareContainer($builder);
        $std = $container->bindInstance('stdClass', new \stdClass());

        $object = $container->get('KoolKode\Context\SetterInjectionExample2');
        $this->assertTrue($object instanceof SetterInjectionExample2);
        $this->assertSame($std, $object->s1);
    }

    public function testFactoryParamWillBeResolvedByNamesResolver()
    {
        $builder = new ContainerBuilder();
        $builder->bind(TestMarker::class)
            ->resolve('name', 'foo')
            ->to(function ($name) {
                return new TestMarker($name);
            });

        $marker = $this->prepareContainer($builder)->get(TestMarker::class);

        $this->assertTrue($marker instanceof TestMarker);
        $this->assertEquals('foo', $marker->name);
    }

    public function testWillInvokeFactoryUsedAsResolverInFactoryBinding()
    {
        $builder = new ContainerBuilder();
        $builder->bind(TestMarker::class)
            ->resolve('name', function () {
                return 'bar';
            })
            ->to(function ($name) {
                return new TestMarker($name);
            });

        $marker = $this->prepareContainer($builder)->get(TestMarker::class);

        $this->assertTrue($marker instanceof TestMarker);
        $this->assertEquals('bar', $marker->name);
    }

    public function testWillInjectScopedConfigIntoFactoryArgument()
    {
        $data = [
            'stdclass' => [
                'message' => 'hello'
            ]
        ];

        $builder = new ContainerBuilder();

        $builder->bind('stdClass')
            ->to(function (Conf\Configuration $config) {
                $std = new \stdClass();
                foreach ($config as $k => $v) {
                    $std->$k = $v;
                }
                return $std;
            });

        $container = $this->prepareContainer($builder);
        $container->setConfiguration(new Configuration($data));

        $object = $container->get('stdClass');
        $this->assertTrue($object instanceof \stdClass);
        $this->assertEquals($object->message, 'hello');
        $this->assertCount(1, (array)$object);
    }

    public function testWillInvokeInitializerAfterFactory()
    {
        $builder = new ContainerBuilder();
        $binding = $builder->bind('stdClass')->to(function () {
            return new \stdClass();
        });

        $object = $this->prepareContainer($builder)->get('stdClass');
        $this->assertTrue($object instanceof \stdClass);
        $this->assertFalse(property_exists($object, 'message'));

        $binding->initialize(function (\stdClass $obj) {
            $obj->message = 'FOO!';
        });
        $object = $this->prepareContainer($builder)->get('stdClass');
        $this->assertTrue($object instanceof \stdClass);
        $this->assertEquals('FOO!', $object->message);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testWillThrowExceptionOnFailingInitializerInFactoryBinding()
    {
        $builder = new ContainerBuilder();
        $builder->bind('stdClass')
            ->to(function () {
                return new \stdClass();
            })
            ->initialize(function (\stdClass $std) {
                throw new \RuntimeException('FAIL!');
            });

        $this->prepareContainer($builder)->get('stdClass');
    }

    /**
     * @expectedException \KoolKode\Context\ContextLookupException
     */
    public function testDetectsCyclicConstructorDependency()
    {
        $container = $this->prepareContainer(new ContainerBuilder());
        $container->get('KoolKode\Context\ConstructorCycle');
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testCanDealWithExceptionThrownFromConstructor()
    {
        $builder = new ContainerBuilder();
        $builder->bind('KoolKode\Context\ConstructorInjectionFail');

        $this->prepareContainer($builder)->get('KoolKode\Context\ConstructorInjectionFail');
    }

    public function testCanCreateTypeUsingFactoryAlias()
    {
        $builder = new ContainerBuilder();
        $builder->bind('stdClass')->to('KoolKode\Context\TestFactory', 'create');

        $object = $this->prepareContainer($builder)->get('stdClass');
        $this->assertTrue($object instanceof \stdClass);
        $this->assertEquals('KoolKode\Context\TestFactory', $object->origin);
        $this->assertCount(1, (array)$object);
    }

    public function testCanCreateTypeUsingFactoryAliasAndConfig()
    {
        $cfg = [
            'stdclass' => [
                'message' => 'Foo!'
            ]
        ];

        $builder = new ContainerBuilder();
        $builder->bind('stdClass')->to('KoolKode\Context\TestFactory', 'create');

        $container = $this->prepareContainer($builder);
        $container->setConfiguration(new Configuration($cfg));

        $object = $container->get('stdClass');
        $this->assertTrue($object instanceof \stdClass);
        $this->assertEquals('KoolKode\Context\TestFactory', $object->origin);
        $this->assertEquals('Foo!', $object->message);
        $this->assertCount(2, (array)$object);
    }

    public function testCanLoadMarkedBindings()
    {
        $builder = new ContainerBuilder();
        $builder->bind('foo')->marked(new TestMarker());
        $builder->bind('bar')->marked(new TestMarker());

        $container = $this->prepareContainer($builder);
        $bindings = $container->eachMarked(function (TestMarker $test, BindingInterface $binding) {
            return $binding->getTypeName();
        });
        sort($bindings);

        $this->assertEquals(['bar', 'foo'], $bindings);
        $this->assertEquals([], $container->eachMarked(function (SetterInjection $inject) {
        }));
    }

    public function testCanRegisterScope()
    {
        $container = $this->prepareContainer(new ContainerBuilder());

        $scope = $this->getMock(ScopeManagerInterface::class);
        $scope->expects($this->exactly(2))->method('getScope')->will($this->returnValue(ApplicationScoped::class));
        $scope->expects($this->once())->method('correlate')->with($container);

        $container->registerScope($scope);

        $tmp = $container->getScope(ApplicationScoped::class);
        $this->assertSame($scope, $tmp);
    }

    /**
     * @expectedException \KoolKode\Context\Scope\ScopeNotFoundException
     */
    public function testCannotLookupUnregisteredScope()
    {
        $this->prepareContainer(new ContainerBuilder())->getScope(ApplicationScoped::class);
    }

    /**
     * @expectedException \KoolKode\Context\Scope\DuplicateScopeException
     */
    public function testCannotRegisterScopeTwice()
    {
        $container = $this->prepareContainer(new ContainerBuilder());
        $container->registerScope(new ApplicationScopeManager());

        $container->registerScope(new ApplicationScopeManager());
    }

    public function testCanRetrieveBoundSingleton()
    {
        $builder = new ContainerBuilder();
        $builder->bind('stdClass')->scoped(new Singleton());

        $container = $this->prepareContainer($builder);
        $binding = $container->getBinding('stdClass');

        $std = $container->getBound($binding);
        $this->assertTrue($std instanceof \stdClass);
        $this->assertSame($std, $container->getBound($binding));
    }

    public function testCanRetrieveBoundApplicationScoped()
    {
        $builder = new ContainerBuilder();
        $builder->bind('stdClass')->scoped(new ApplicationScoped());

        $container = $this->prepareContainer($builder);
        $container->registerScope(new ApplicationScopeManager());
        $binding = $container->getBinding('stdClass');

        $std = $container->getBound($binding);
        $this->assertTrue($std instanceof \stdClass);
        $this->assertSame($std, $container->getBound($binding));
    }

    /**
     * @expectedException \KoolKode\Context\Scope\ScopeNotFoundException
     */
    public function testThrowsExceptionOnMissingBoundScope()
    {
        $builder = new ContainerBuilder();
        $builder->bind('stdClass')->scoped(new ApplicationScoped());

        $container = $this->prepareContainer($builder);
        $binding = $container->getBinding('stdClass');

        $container->getBound($binding);
    }

    public function testCanRetrieveBoundAlias()
    {
        $builder = new ContainerBuilder();
        $builder->bind('Foo')->toAlias('stdClass');
        $builder->bind('stdClass')->scoped(new Singleton());

        $container = $this->prepareContainer($builder);
        $std = $container->getBound($container->getBinding('Foo'));
        $this->assertTrue($std instanceof \stdClass);
        $this->assertSame($std, $container->get('stdClass'));
    }

    public function testCanRetrieveBoundSingletonUsingFactory()
    {
        $builder = new ContainerBuilder();
        $builder->bind('stdClass')->scoped(new Singleton())->to(function () {
            return new \stdClass();
        });

        $container = $this->prepareContainer($builder);
        $std = $container->get('stdClass');
        $this->assertTrue($std instanceof \stdClass);
        $this->assertSame($std, $container->get('stdClass'));
    }

    public function createStd()
    {
        return new \stdClass();
    }

    public function testCanRetrieveBoundSingletonUsingFactoryAlias()
    {
        $builder = new ContainerBuilder();
        $builder->bind('stdClass')->scoped(new Singleton())->to(get_class($this), 'createStd');

        $container = $this->prepareContainer($builder);
        $container->bindInstance(get_class($this), $this);

        $std = $container->get('stdClass');
        $this->assertTrue($std instanceof \stdClass);
        $this->assertSame($std, $container->get('stdClass'));
    }

    public function testWillNotThrowExceptionOnOptionalInjectionPoint()
    {
        $builder = new ContainerBuilder();
        $builder->bind('stdClass')
            ->to(function (InjectionPointInterface $point = NULL) {
                return new \stdClass();
            });

        $container = $this->prepareContainer($builder);

        $this->assertTrue($container->get('stdClass') instanceof \stdClass);
    }

    /**
     * @expectedException \KoolKode\Context\ContextLookupException
     */
    public function testWillDetectMissingInjectionPoint()
    {
        $builder = new ContainerBuilder();
        $builder->bind('stdClass')
            ->to(function (InjectionPointInterface $point) {
                return new \stdClass();
            });

        $this->prepareContainer($builder)->get('stdClass');
    }

    public function testWillPassInjectionPointToInlineFactory()
    {
        $builder = new ContainerBuilder();
        $builder->bind('stdClass')
            ->to(function (InjectionPointInterface $point) {
                return $point;
            });

        $container = $this->prepareContainer($builder);
        $point = new InjectionPoint(get_class($this), 'injectSomething');

        $this->assertSame($point, $container->get('stdClass', $point));
    }

    public function testWillUseInjectionPointsInConstructorAndSetterInjection()
    {
        $builder = new ContainerBuilder();

        $builder->bind('KoolKode\Context\TestInjector')
            ->marked(new SetterInjection(SetterInjection::CONVENTION));

        $builder->bind('stdClass')
            ->to(function (InjectionPointInterface $point) {
                $std = new \stdClass();
                $std->point = $point;
                return $std;
            });

        $container = $this->prepareContainer($builder);

        $injector = $container->get('KoolKode\Context\TestInjector');
        $this->assertTrue($injector->foo instanceof \stdClass);
        $this->assertTrue($injector->bar instanceof \stdClass);

        $this->assertTrue($injector->foo->point instanceof InjectionPointInterface);
        $this->assertTrue($injector->bar->point instanceof InjectionPointInterface);

        $this->assertEquals('KoolKode\Context\TestInjector', $injector->foo->point->getTypeName());
        $this->assertEquals('__construct', $injector->foo->point->getMethodName());

        $this->assertEquals('KoolKode\Context\TestInjector', $injector->bar->point->getTypeName());
        $this->assertEquals('injectBar', $injector->bar->point->getMethodName());
    }

    public function testDecoratorsCanReplaceObject()
    {
        $builder = new ContainerBuilder();

        $builder->bind('stdClass')->to(get_class($this), 'createStdClassTestObject')->decorate(function (\stdClass $obj) {
            return $obj->container;
        });

        $container = $this->prepareContainer($builder);
        $container->bindInstance(get_class($this), $this);

        $obj = $container->get(\stdClass::class);
        $this->assertTrue($obj instanceof ContainerInterface);
        $this->assertSame($container, $obj);
    }

    public function createStdClassTestObject(ContainerInterface $container)
    {
        $std = new \stdClass();
        $std->container = $container;

        return $std;
    }

    public function testCanApplyMultiplePrioritizedDecorators()
    {
        $builder = new ContainerBuilder();

        $builder->bind('stdClass')->decorate(function (\stdClass $obj, ContainerInterface $container) {
            return $container;
        }, 10)->decorate(function (ContainerInterface $obj) {
            return $obj->get('test');
        });

        $container = $this->prepareContainer($builder);
        $container->bindInstance('test', $this);

        $obj = $container->get(\stdClass::class);
        $this->assertSame($this, $obj);
    }

    public function testWillExpandInlinedNamesInInstanceofChecks()
    {
        $builder = new ContainerBuilder();

        $builder->bind('stdClass')
            ->to(function (ContainerInterface $container) {
                $std = new \stdClass();
                $std->compiled = ($container instanceof CompiledContainer);

                return $std;
            });

        $container = $this->prepareContainer($builder);
        $obj = $container->get(\stdClass::class);

        $this->assertInstanceOf(\stdClass::class, $obj);
        $this->assertEquals($container instanceof CompiledContainer, $obj->compiled);
    }

    public function testWillExpandClassConstantLookups()
    {
        $builder = new ContainerBuilder();

        $builder->bind(ContainerBuilder::class)
            ->to(function (ContainerInterface $container) {

                $typeName = ContainerBuilder::class;
                return new $typeName();
            });

        $container = $this->prepareContainer($builder);
        $obj = $container->get(ContainerBuilder::class);

        $this->assertInstanceOf(ContainerBuilder::class, $obj);
    }

    public function testWillExpandTypeNamesInCatchStatement()
    {
        $builder = new ContainerBuilder();

        $builder->bind(ContainerBuilder::class)
            ->to(function (ContainerInterface $container) {
                try {
                    throw new ScopeNotFoundException('Nope!');
                } catch (ScopeNotFoundException $e) {
                    return new \stdClass();
                }
            });

        $container = $this->prepareContainer($builder);
        $obj = $container->get(ContainerBuilder::class);

        $this->assertInstanceOf(\stdClass::class, $obj);
    }

    public function testWillExpandTypeNamesOfFunctionArguments()
    {
        $builder = new ContainerBuilder();

        $builder->bind(ContainerBuilder::class)
            ->to(function (ContainerInterface $container) {
                return call_user_func(function (ContainerInterface $obj) {
                    $std = new \stdClass();
                    $std->container = $obj;

                    return $std;
                }, $container);
            });

        $container = $this->prepareContainer($builder);
        $obj = $container->get(ContainerBuilder::class);

        $this->assertInstanceOf(\stdClass::class, $obj);
        $this->assertSame($container, $obj->container);
    }

    public function test()
    {
        $config = new Configuration([
            'stdclass' => [
                'setting' => 'foo'
            ]
        ]);

        $builder = new ContainerBuilder();
        $builder->bind(\stdClass::class)
            ->initialize(function ($obj, Configuration $config) {
                $obj->setting = $config->getString('setting');
            });

        $container = $this->prepareContainer($builder);
        $container->setConfiguration($config);

        $obj = $container->get(\stdClass::class);
        $this->assertInstanceOf(\stdClass::class, $obj);
        $this->assertEquals('foo', $obj->setting);
    }
}
