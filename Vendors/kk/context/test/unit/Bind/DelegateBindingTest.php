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

/**
 * @covers \KoolKode\Context\Bind\DelegateBinding
 */
class DelegateBindingTest extends \PHPUnit_Framework_TestCase
{
    public function testCanUseDelegateBinding()
    {
        $callback = function () {
            return 'FOO';
        };
        $options = BindingInterface::TYPE_FACTORY;

        $binding = new DelegateBinding('stdClass', ApplicationScoped::class, $options, $callback);
        $this->assertEquals('stdClass', $binding->getTypeName());
        $this->assertEquals($options, $binding->getOptions());
        $this->assertEquals(ApplicationScoped::class, $binding->getScope());
        $this->assertSame($callback, $binding->getCallback());
        $this->assertEquals('FOO', $binding(new Container()));

        $this->assertEquals([
            'typeName' => \stdClass::class,
            'scope' => ApplicationScoped::class,
            'options' => $options
        ], $binding->__debugInfo());
    }
}
