<?php

/*
 * This file is part of KoolKode Context and Dependency Injection.
*
* (c) Martin Schröder <m.schroeder2007@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace KoolKode\Context\Scope;

use KoolKode\Context\Bind\ContainerBuilder;
use KoolKode\Context\Container;

/**
 * @covers \KoolKode\Context\Scope\ScopedProxyTrait
 */
class ScopedProxyTraitTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Container
     */
    protected $container;

    /**
     * @var FoxyProxy
     */
    protected $proxy;

    /**
     * @expectedException \RuntimeException
     */
    public function testCannotSerializeScopedProxy()
    {
        serialize($this->proxy);
    }

    public function testCanCallMethodusingProxy()
    {
        $ref = new \ReflectionClass(get_class($this->proxy));
        $trait = array_values($ref->getTraits())[0];

        $this->assertTrue($this->proxy instanceof ScopedProxyInterface);
        $this->assertEquals('KoolKode\Context\Scope\ScopedProxyTrait', $trait->name);
        $this->assertEquals('Hello from ' . $this->proxy->K2GetProxyBinding()->getTypeName(), $this->proxy->doSomething());
    }

    public function testCanCheckForFieldsUsingMagicIsset()
    {
        $this->assertTrue($this->proxy->__isset('message'));
        $this->assertFalse(isset($this->proxy->bar));
    }

    public function testCanAccessFieldsUsingMagicGet()
    {
        $value = $this->proxy->message;
        $this->assertEquals('Hello world', $value);
    }

    public function testCanSetFieldUsingMagicSet()
    {
        $message = 'This is foo!';
        $this->proxy->message = $message;
        $this->assertEquals($message, $this->proxy->message);
        $this->assertEquals($message, $this->proxy->K2UnwrapScopedProxy()->message);
    }

    public function provideArgs()
    {
        return [
            [[]],
            [['foo']],
            [['foo', 'foo']],
            [['foo', 'foo', 'foo']],
            [['foo', 'foo', 'foo', 'foo']],
            [['foo', 'foo', 'foo', 'foo', 'foo']],
            [['foo', 'foo', 'foo', 'foo', 'foo', 'foo']]
        ];
    }

    /**
     * @dataProvider provideArgs
     */
    public function testCallMagicGet(array $args)
    {
        $result = call_user_func_array([$this->proxy, 'countArgs'], $args);
        $this->assertEquals(count($args), $result);
    }

    protected function setUp()
    {
        parent::setUp();

        $builder = new ContainerBuilder();

        $builder->bind('KoolKode\Context\Scope\FoxyProxy')
            ->scoped(new ApplicationScoped());

        $this->container = $builder->build();
        $this->container->registerScope(new ApplicationScopeManager());

        $this->proxy = $this->container->get('KoolKode\Context\Scope\FoxyProxy');
    }
}
