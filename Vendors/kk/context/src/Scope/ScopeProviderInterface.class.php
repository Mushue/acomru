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

use KoolKode\Context\Bind\ContainerModuleInterface;

/**
 * Provides additional scope that are automatically correlated to the DI container.
 *
 * @author Martin Schröder
 */
interface ScopeProviderInterface extends ContainerModuleInterface
{
    /**
     * Register custom scope implementations with the DI container.
     *
     * @param ScopeLoader $loader
     */
    public function loadScopes(ScopeLoader $loader);
}
