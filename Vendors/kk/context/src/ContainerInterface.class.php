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

use KoolKode\Config\Configuration;
use KoolKode\Context\Bind\BindingInterface;

/**
 * DI container that allows for configuration using bindings, bound type location using markers and
 * retrieval of method arguments in order to facilitate constructor / setter injection.
 *
 * @author Martin Schröder
 */
interface ContainerInterface
{
    /**
     * Checks if the given parameter is set in the container.
     *
     * @param string $name The name of the context param.
     * @return boolean Will return true if the param exists (even if the param's value evaluates to false).
     */
    public function hasParameter($name);

    /**
     * Get the value of a container parameter.
     *
     * @param string $name The name of the context param.
     * @param mixed $default An optional default value to be used.
     * @return mixed The resolved context param value.
     *
     * @throws ContextParamNotFoundException When the param was not found and no default value was given.
     */
    public function getParameter($name);

    /**
     * Get all context parameters registered with the container.
     *
     * @return array<string, mixed> All registered context params.
     */
    public function getParameters();

    /**
     * Get the configuration instance bound to the DI container.
     *
     * @return Configuration
     */
    public function getConfiguration();

    /**
     * Bind an instance of a type to the container.
     *
     * @param string $typeName The name of the type to be bound.
     * @param object $instance The object instance to bind.
     * @param boolean $local Create a local instance (that is a reference that is not stored in Singleton scope)?
     * @return object The bound instance.
     */
    public function bindInstance($typeName, $instance, $local = false);

    /**
     * Get a contextual object instance from the container, this may return a scoped proxy depending
     * on the scope of the bound object.
     *
     * @param string $typeName The name of the bound type.
     * @param InjectionPointInterface $point
     * @return object The resolved bound object.
     *
     * @throws ContextLookupException When no contextual instance could be resolved.
     */
    public function get($typeName, InjectionPointInterface $point = NULL);

    /**
     * Get the binding for the given type from the container.
     *
     * @param string $typeName The name of the bound type.
     * @return BindingInterface The registered binding for the type.
     *
     * @throws TypeNotBoundException When no binding is registered for the given type.
     */
    public function getBinding($typeName);

    /**
     * Get a contextual object instance of the given binding, this may return a scoped proxy depending
     * on the scope of the binding.
     *
     * @param BindingInterface $binding The binding to be used for resolving a contextual instance.
     * @param InjectionPointInterface $point
     * @return object The resolved bound object.
     *
     * @throws ContextLookupException When no contextual instance could be resolved for the given binding.
     */
    public function getBound(BindingInterface $binding, InjectionPointInterface $point = NULL);

    /**
     * Collect all bindings that have a marker of the type that corresponds to the first
     * argument of the given callback and apply the callback for each marker passing the
     * marker as first argument and an instance of BindingInterface as second argument.
     *
     * All values returned from the callback invocation(s) are collected in an array and
     * returned by this method.
     *
     * @param callable $callback
     * @return array
     */
    public function eachMarked(callable $callback);
}
