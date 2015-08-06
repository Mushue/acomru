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
 * Provides management capabilities for contextual instances within a DI container.
 *
 * Each implementation of a scope may provide additional methods to control when a
 * scope is entered (becomes active) and is left (will be destroyed).
 *
 * @author Martin Schröder
 */
interface ScopeManagerInterface
{
    /**
     * Get the FQN of the scope marker
     *
     * @return string
     */
    public function getScope();

    /**
     * Check if this scope is active.
     *
     * @return boolean
     */
    public function isActive();

    /**
     * Get the DI container being employed by this scope.
     *
     * @return ScopedContainerInterface The DI container instance being used.
     */
    public function getContainer();

    /**
     * Correlate to the scope to the container (called immediately after the scope
     * is registered with a container).
     *
     * @param ScopedContainerInterface $container
     */
    public function correlate(ScopedContainerInterface $container);

    /**
     * Remove all data associated with this scope.
     */
    public function clear();

    /**
     * Lookup a contextual instance, use the given factory callback to create it.
     *
     * @param string $typeName
     * @param callable $factory
     */
    public function lookup($typeName, callable $factory);
}
