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

use KoolKode\Context\Bind\BindingInterface;
use KoolKode\Context\ContainerInterface;

/**
 * Service locator based on a DI container and bindings, will lazy-load all services as needed.
 *
 * @author Martin Schröder
 */
class BindingServiceLocator implements ServiceLocatorInterface
{
    /**
     * Holds all registered bindings by name.
     *
     * @var array<string, BindingInterface>
     */
    protected $bindings = [];

    /**
     * Holds all service instances that have been created.
     *
     * @var array<string, object>
     */
    protected $bound = [];

    /**
     * Holds a reference to the DI container backing this locator.
     *
     * @var ContainerInterface
     */
    protected $container;

    /**
     * Share / cache instances and re-use them later on?
     *
     * @var boolean
     */
    protected $shared;

    /**
     * Create a binding service locator backed by the given DI container.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container, $shared = false)
    {
        $this->container = $container;
        $this->shared = $shared ? true : false;
    }

    public function __debugInfo()
    {
        return [
            'services' => array_keys($this->bindings)
        ];
    }

    /**
     * Count registered services (counts bindings hence it does not fetch instances
     * from the DI container).
     *
     * @return integer
     */
    public function count()
    {
        return count($this->bindings);
    }

    /**
     * Provides an iterator on top of all registered services, obtaining the iterator triggers
     * loading of ALL registered services from the DI container.
     *
     * @return \ArrayIterator<string, object>
     */
    public function getIterator()
    {
        if ($this->shared) {
            foreach ($this->bindings as $key => $binding) {
                if (empty($this->bound[$key])) {
                    $this->bound[$key] = $this->container->getBound($binding);
                }
            }

            return new \ArrayIterator($this->bound);
        }

        $bound = [];

        foreach ($this->bindings as $key => $binding) {
            $bound[$key] = $this->container->getBound($binding);
        }

        return new \ArrayIterator($bound);
    }

    /**
     * {@inheritdoc}
     */
    public function getService($name)
    {
        if ($this->shared) {
            if (empty($this->bound[$name])) {
                if (empty($this->bindings[$name])) {
                    throw new ServiceNotFoundException(sprintf('Service "%s" is not registered', $name));
                }

                $this->bound[$name] = $this->container->getBound($this->bindings[$name]);
            }

            return $this->bound[$name];
        }

        if (empty($this->bindings[$name])) {
            throw new ServiceNotFoundException(sprintf('Service "%s" is not registered', $name));
        }

        return $this->container->getBound($this->bindings[$name]);
    }

    /**
     * Register a binding in the locator, will use the name of the bound type when no
     * name for the service is given.
     *
     * @param BindingInterface $binding The binding being used to load the service.
     * @param string $name Optional name of the registered service.
     *
     * @throws DuplicateServiceRegistrationException
     */
    public function registerService(BindingInterface $binding, $name = NULL)
    {
        $key = ($name === NULL) ? $binding->getTypeName() : (string)$name;

        if (isset($this->bindings[$key])) {
            throw new DuplicateServiceRegistrationException(sprintf('Service "%s" is already registered', $key));
        }

        $this->bindings[$key] = $binding;
    }
}
