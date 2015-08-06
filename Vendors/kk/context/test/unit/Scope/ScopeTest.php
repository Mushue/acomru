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

/**
 * @covers \KoolKode\Context\Scope\Scope
 */
class ScopeTest extends \PHPUnit_Framework_TestCase
{
    public function testCanUnwrapScopedProxy()
    {
        $foo = new \stdClass();

        $this->assertSame($foo, Scope::unwrap(new ScopedProxyMock('foo', $foo)));
    }

    public function testReturnsNonProxyObjectAsIs()
    {
        $foo = new \stdClass();

        $this->assertSame($foo, Scope::unwrap($foo));
    }
}
