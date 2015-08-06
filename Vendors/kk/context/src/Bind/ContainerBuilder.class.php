<?php

/*
 * This file is part of KoolKode Context and Dependency Injection.
*
* (c) Martin Schröder <m.schroeder2007@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace KoolKode\Context\Bind;

use KoolKode\Context\Container;
use KoolKode\Context\ContextParamNotFoundException;
use KoolKode\Context\Scope\Singleton;

/**
 * Builds a DI container using registered bindings and parameters.
 *
 * @author Martin Schröder
 */
class ContainerBuilder
{
    /**
     * Registered container params.
     *
     * @var array<string, mixed>
     */
    protected $parameters = [];

    /**
     * Registered container bindings.
     *
     * @var array<string, Binding>
     */
    protected $bindings = [];

    /**
     * Keeps track of all types that need a scoped proxy due to a binding.
     *
     * @var array<string, boolean>
     */
    protected $proxyTypes;

    /**
     * Provides access to all registered container initializers.
     *
     * @var ContainerInitializerLoader
     */
    protected $initializers;

    /**
     * Create a new DI container builder using the given initializers.
     *
     * @param ContainerInitializerLoader $initializers
     */
    public function __construct(ContainerInitializerLoader $initializers = NULL)
    {
        $this->initializers = ($initializers === NULL) ? new ContainerInitializerLoader() : $initializers;
    }

    /**
     * Get all registered container initializers.
     *
     * @return array<ContainerInitializerInterface>
     */
    public function getInitializers()
    {
        return $this->initializers->toArray();
    }

    /**
     * Check if the given parameter has been registered.
     *
     * @param string $name
     * @return boolean
     */
    public function hasParameter($name)
    {
        return array_key_exists((string)$name, $this->parameters);
    }

    /**
     * Get the value of the given parameter.
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     *
     * @throws ContextParamNotFoundException When the parameter has not been registered and no default value is given.
     */
    public function getParameter($name)
    {
        if (array_key_exists($name, $this->parameters)) {
            return $this->parameters[$name];
        }

        if (func_num_args() > 1) {
            return func_get_arg(1);
        }

        throw new ContextParamNotFoundException(sprintf('Container parameter "%s" was not found', $name));
    }

    /**
     * Get all registered container params.
     *
     * @return array<string, mixed>
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * Populate a parameter in the container builder.
     *
     * @param string $name
     * @param mixed $value
     * @return ContainerBuilder
     */
    public function setParameter($name, $value)
    {
        $this->parameters[(string)$name] = $value;

        return $this;
    }

    /**
     * Remove a registered parameter from the container.
     *
     * @param string $name
     * @return ContainerBuilder
     */
    public function removeParameter($name)
    {
        unset($this->parameters[(string)$name]);

        return $this;
    }

    /**
     * Check if a binding is registered for the given type.
     *
     * @param string $typeName
     * @return boolean
     */
    public function isBound($typeName)
    {
        return isset($this->bindings[$typeName]);
    }

    /**
     * Create a binding for the given type, the binding is registered in the builder
     * and will be bound by the created DI container, binding multiple times to the same type
     * will modify the existing binding instead of creating a new binding!
     *
     * @return Binding
     */
    public function bind($typeName)
    {
        if ($this->proxyTypes !== NULL) {
            $this->proxyTypes = NULL;
        }

        $key = strtolower($typeName);

        switch ($key) {
            case 'kk\config\configuration':
            case 'kk\context\containerinterface':
            case 'kk\context\exposedcontainerinterface':
            case 'kk\context\scope\scopedcontainerinterface':
                throw new \InvalidArgumentException(sprintf('Binding %s is forbidden', $typeName));
        }

        if (isset($this->bindings[$key])) {
            return $this->bindings[$key];
        }

        return $this->bindings[$key] = new Binding($typeName);
    }

    /**
     * Get all bindings registered with the builder.
     *
     * @return array<string, Binding>
     */
    public function getBindings()
    {
        return $this->bindings;
    }

    /**
     * Check if a scoped proxy has to be generated for the given type.
     *
     * @param string $typeName
     * @return boolean
     */
    public function isScopedProxyRequired($typeName)
    {
        return array_key_exists(strtolower($typeName), $this->getProxyBindings());
    }

    /**
     * Get all bindings that require a scope proxy.
     *
     * @return \array<string, BindingInterface>
     */
    public function getProxyBindings()
    {
        if ($this->proxyTypes === NULL) {
            $this->proxyTypes = [];

            foreach ($this->bindings as $binding) {
                $scope = $binding->getScope();

                if ($scope === NULL) {
                    continue;
                }

                if (Singleton::class == $scope) {
                    continue;
                }

                $this->proxyTypes[strtolower($binding->getTypeName())] = $binding;
            }
        }

        return $this->proxyTypes;
    }

    /**
     * Build a new DI container from the bindings and parameters in this builder.
     *
     * @return ContainerInterface
     */
    public function build()
    {
        return new Container($this->parameters, $this->initializers->toArray(), $this->bindings);
    }
}
