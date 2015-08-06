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
 * @covers \KoolKode\Context\Scope\ScopeLoader
 */
class ScopeLoaderTest extends \PHPUnit_Framework_TestCase
{
    public function testCanCreateEmptyScopeLoader()
    {
        $loader = new ScopeLoader();

        $this->assertCount(0, $loader);
        $this->assertEquals([], $loader->toArray());
        $this->assertEquals([], iterator_to_array($loader->getIterator()));
    }

    public function testCanRegisterScope()
    {
        $scope = $this->getMock(ScopeManagerInterface::class);
        $scope->expects($this->exactly(2))->method('getScope')->will($this->returnValue(4));

        $loader = new ScopeLoader();
        $loader->registerScope($scope);

        $this->assertCount(1, $loader);
        $this->assertSame($scope, $loader->toArray()[4]);
    }

    /**
     * @expectedException \KoolKode\Context\Scope\DuplicateScopeException
     */
    public function testThrowsExceptionOnDuplicateScopeRegistration()
    {
        $scope = $this->getMock(ScopeManagerInterface::class);
        $scope->expects($this->any())->method('getScope')->will($this->returnValue(8));

        $loader = new ScopeLoader();
        $loader->registerScope($scope);

        $loader->registerScope($scope);
    }

    public function testCanComputeScopeHash()
    {
        $loader = new ScopeLoader();
        $loader->registerScope(new SingletonScopeManager());
        $loader->registerScope(new ApplicationScopeManager());

        $hash = $loader->getHash();
        $this->assertEquals($hash, $loader->getHash());
    }
}
