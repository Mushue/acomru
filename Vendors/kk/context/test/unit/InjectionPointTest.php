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

/**
 * @covers \KoolKode\Context\InjectionPoint
 */
class InjectionPointTest extends \PHPUnit_Framework_TestCase
{
    public function testWillUseConstructorParams()
    {
        $point = new InjectionPoint('foo', 'bar');
        $this->assertEquals('foo', $point->getTypeName());
        $this->assertEquals('bar', $point->getMethodName());
    }
}
