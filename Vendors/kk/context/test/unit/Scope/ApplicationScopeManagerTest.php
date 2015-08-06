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
 * @covers \KoolKode\Context\Scope\ApplicationScopeManager
 */
class ApplicationScopeManagerTest extends \PHPUnit_Framework_TestCase
{
    public function testApplicationScopeIsActiveAfterCreation()
    {
        $scope = new ApplicationScopeManager();
        $this->assertFalse($scope->isActive());

        (new Container())->registerScope($scope);
        $this->assertTrue($scope->isActive());
    }

    public function testProxiesAreRegisteredWithContainer()
    {
        $container = new Container();

        $scope = new ApplicationScopeManager();
        $this->assertFalse($container->hasProxy(ApplicationScopeManager::class));

        $container->registerScope($scope);
        $this->assertTrue($container->hasProxy(ApplicationScopeManager::class));
    }

    public function testScopeIsActiveAfterClear()
    {
        $container = new Container();
        $scope = new ApplicationScopeManager();
        $container->registerScope($scope);

        $this->assertTrue($scope->isActive());
        $scope->clear();
        $this->assertTrue($scope->isActive());
    }
}
