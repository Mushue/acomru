<?php

/*
 * This file is part of KoolKode Utilities.
*
* (c) Martin Schröder <m.schroeder2007@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace KoolKode\Util;

/**
 * @covers \KoolKode\Util\ReflectionTrait
 */
class ReflectionTraitTest extends \PHPUnit_Framework_TestCase
{
    public function testCanGetTypeOfInternalClass()
    {
        $params = (new \ReflectionMethod('KoolKode\Util\TestReflection', 'test1'))->getParameters();

        $trait = new TestReflection();
        $this->assertEquals('stdClass', $trait->getType($params[0]));
    }

    public function testMissingTypeHintYieldsNull()
    {
        $params = (new \ReflectionMethod('KoolKode\Util\TestReflection', 'test2'))->getParameters();

        $trait = new TestReflection();
        $this->assertNull($trait->getType($params[0]));
    }

    public function testTypeHintedArrayIsDetected()
    {
        $params = (new \ReflectionMethod('KoolKode\Util\TestReflection', 'test3'))->getParameters();

        $trait = new TestReflection();
        $this->assertEquals('array', $trait->getType($params[0]));
    }

    public function testTypeHintedCallableIsDetected()
    {
        $params = (new \ReflectionMethod('KoolKode\Util\TestReflection', 'test4'))->getParameters();

        $trait = new TestReflection();
        $this->assertEquals('callable', $trait->getType($params[0]));
    }

    public function testWillResolveSelfToTypeName()
    {
        if (defined('HHVM_VERSION')) {
            return $this->markTestSkipped('HHVM currently not able to reflect "self" typehint');
        }

        $params = (new \ReflectionMethod('KoolKode\Util\TestReflection', 'test5'))->getParameters();

        $trait = new TestReflection();
        $this->assertEquals('KoolKode\Util\TestReflection', $trait->getType($params[0]));
    }

    public function testWillResolveSelfInClosureParam()
    {
        if (defined('HHVM_VERSION')) {
            return $this->markTestSkipped('HHVM currently not able to reflect "self" typehint');
        }

        $params = call_user_func(\Closure::bind(function () {
            return (new \ReflectionFunction(function (self $foo) {
            }))->getParameters();
        }, NULL, __CLASS__));

        $trait = new TestReflection();
        $this->assertEquals(__CLASS__, $trait->getType($params[0]));
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testCannotresolveSelfWhenNotBoundToClassScope()
    {
        if (defined('HHVM_VERSION')) {
            return $this->markTestSkipped('HHVM currently not able to reflect "self" typehint');
        }

        $params = call_user_func(\Closure::bind(function () {
            return (new \ReflectionFunction(function (self $foo) {
            }))->getParameters();
        }, NULL, NULL));

        $trait = new TestReflection();
        $this->assertEquals(__CLASS__, $trait->getType($params[0]));
    }
}
