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

use KoolKode\Context\Bind\BindingInterface;
use KoolKode\Context\TypeNotFoundException;

/**
 * Provides management capabilities for contextual instances within a DI container.
 *
 * Each implementation of a scope may provide additional methods to control when a
 * scope is entered (becomes active) and is left.
 *
 * @author Martin Schröder
 */
interface ProxyScopeManagerInterface extends ScopeManagerInterface
{
    /**
     * Fully-qualified names of additional types that need a scoped proxy.
     *
     * @return array<string>
     */
    public function getProxyTypeNames();

    /**
     * Create a registered proxy object bound to the scope and container.
     *
     * @param BindingInterface $binding The bound type to be proxied.
     * @return ScopedProxyInterface The generated scoped proxy of the bound type.
     *
     * @throws TypeNotFoundException When the bound type (class / interface) could not be loaded.
     */
    public function createProxy(BindingInterface $binding);

    /**
     * Activate a contextual instance being proxied.
     *
     * @param ScopedProxyInterface $proxy The scoped proxy to be activated.
     * @return object The contextual instance being proxied.
     *
     * @throws ScopeNotActiveException When this scope has not been entered.
     */
    public function activateInstance(ScopedProxyInterface $proxy);
}
