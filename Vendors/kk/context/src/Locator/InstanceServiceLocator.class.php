<?php

/*
 * This file is part of KoolKode Context and Dependency Injection.
*
* (c) Martin Schröder <m.schroeder2007@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace KoolKode\Context\Locator;

/**
 * Basic service locator implementation that maps object instances to service names.
 *
 * @author Martin Schröder
 */
class InstanceServiceLocator implements ServiceLocatorInterface
{
    /**
     * Holds all registered services.
     *
     * @var array<string, object>
     */
    protected $services = [];

    public function __debugInfo()
    {
        return [
            'services' => array_keys($this->services)
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return count($this->services);
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->services);
    }

    /**
     * {@inheritdoc}
     */
    public function getService($name)
    {
        if (empty($this->services[$name])) {
            throw new ServiceNotFoundException(sprintf('Service "%s" is not registered', $name));
        }

        return $this->services[$name];
    }

    /**
     * Register the given object instance under the given name, defaults to the fully-qualified
     * type name of the object instance when no name is given.
     *
     * @param object $object The object instance to be registered.
     * @param string $name An optional name for the service.
     *
     * @throws \InvalidArgumentException When no object instance has been given.
     * @throws DuplicateServiceRegistrationException
     */
    public function registerService($object, $name = NULL)
    {
        if (!is_object($object)) {
            throw new \InvalidArgumentException(sprintf('Expecting an object, given %s', gettype($object)));
        }

        $key = ($name === NULL) ? get_class($object) : (string)$name;

        if (isset($this->services[$key])) {
            throw new DuplicateServiceRegistrationException(sprintf('Service "%s" is already registered', $key));
        }

        $this->services[$key] = $object;
    }
}
