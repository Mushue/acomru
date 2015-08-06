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

use KoolKode\Context\Bind\Marker;

/**
 * Contract for a container that exposes additional methods that are not required when
 * using a DI container in custom code but may come in handy when implementing components
 * that are tightly coupled to the DI container.
 *
 * @author Martin Schröder
 */
interface ExposedContainerInterface extends ContainerInterface
{
    /**
     * Check if the given object instance is a proxy (or bound object) aquired through the container.
     *
     * @param object $object
     * @return boolean
     */
    public function isProxy($object);

    /**
     * Get the bound type name of the given proxy.
     *
     * @param object $object
     * @return string The fully-qualified name of the bound type or NULL when none was found.
     */
    public function getBoundTypeOfProxy($object);

    /**
     * Checks if a proxy is bound for the given type name.
     *
     * @param string $typeName
     * @return boolean
     */
    public function hasProxy($typeName);

    /**
     * Populate an array of arguments for the given callback.
     *
     * @param \ReflectionFunctionAbstract $ref The callback to be invoked.
     * @param integer $skip Number of leading arguments to be skipped.
     * @param array <string, \Closure> $resolvers Param resolvers to be matched by name.
     * @param InjectionPointInterface $point Injection point to be assumed.
     * @return array Generated arguments array.
     *
     * @throws ContextLookupException When the container was not able to resolve all parameters.
     */
    public function populateArguments(\ReflectionFunctionAbstract $ref, $skip = 0, array $resolvers = NULL, InjectionPointInterface $point = NULL);

    /**
     * Create an instance of the given type using constructor param resolvers.
     *
     * @param string $typeName
     * @param array <string, mixed> $resolvers
     *
     * @throws TypeNotFoundException When the given type could not be loaded.
     */
    public function createObject($typeName, array $resolvers = NULL);
}
