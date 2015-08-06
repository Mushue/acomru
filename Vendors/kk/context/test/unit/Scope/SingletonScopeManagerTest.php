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

use KoolKode\Context\Container;

/**
 * @covers \KoolKode\Context\Scope\SingletonScopeManager
 */
class SingletonScopeManagerTest extends \PHPUnit_Framework_TestCase
{
    public function testScopeActivatedByCorrelation()
    {
        $scope = new SingletonScopeManager();
        $this->assertFalse($scope->isActive());
        $this->assertNull($scope->getContainer());

        $container = new Container();
        $scope->correlate($container);
        $this->assertTrue($scope->isActive());
        $this->assertSame($container, $scope->getContainer());
        $this->assertSame($scope, $container->get(SingletonScopeManager::class));
    }

    public function testClearWillRemoveInstances()
    {
        $scope = new SingletonScopeManager();
        $scope->correlate(new Container());

        $std = new \stdClass();
        $scope->register('foo', $std);
        $this->assertSame($std, $scope->lookup('foo', function () {
            return 'N/A';
        }));

        $scope->clear();
        $this->assertEquals('N/A', $scope->lookup('foo', function () {
            return 'N/A';
        }));
    }
}
