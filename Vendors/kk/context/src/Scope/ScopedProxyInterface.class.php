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
 * Scoped proxies are wrappers around contextual objects managed by a DI container scope.
 *
 * @author Martin Schröder
 */
interface ScopedProxyInterface extends \Serializable
{
    /**
     * Get the contextual instance from the active scope. Do not pass the returned object
     * around because the scope might change and this would lead to references to a wrong
     * object instance being used.
     *
     * @return object
     *
     * @throws ScopeNotActiveException
     */
    public function K2UnwrapScopedProxy();

    /**
     * Get the binding of this scoped proxy.
     *
     * @return BindingInterface
     */
    public function K2GetProxyBinding();

    /**
     * Check if the proxy is bound (active) within it's current scope.
     *
     * @return booelan
     */
    public function K2IsProxyBound();
}
