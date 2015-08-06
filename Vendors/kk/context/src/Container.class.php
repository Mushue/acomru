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
use KoolKode\Context\Bind\Binding;
use KoolKode\Context\Bind\BindingInterface;
use KoolKode\Context\Bind\ContainerInitializerInterface;
use KoolKode\Context\Bind\SetterInjection;
use KoolKode\Context\Scope\ApplicationScoped;
use KoolKode\Context\Scope\ApplicationScopeManager;
use KoolKode\Context\Scope\ProxyScopeManagerInterface;
use KoolKode\Context\Scope\ScopeNotFoundException;
use KoolKode\Context\Scope\ScopedProxyInterface;

/**
 * Container implementation based on bindings and reflection, is primarily used in CLI applications
 * such as unit tests and commands.
 *
 * @author Martin Schröder
 */
class Container extends AbstractContainer
{
    /**
     * Maps bindings by type name.
     *
     * @var array<string, Binding>
     */
    protected $bindings = [];

    /**
     * Maps bindings grouped by qualifier type name.
     *
     * @var array<string, array<Binding>>
     */
    protected $markers = [];

    /**
     * Create a DI container using the given parameters, initializers and active bindings.
     *
     * @param array <string, mixed> $parameters
     * @param array <ContainerInitializerInterface> $initializers
     * @param array <Binding> $bindings
     */
    public function __construct(array $parameters = [], array $initializers = [], array $bindings = [])
    {
        parent::__construct($parameters, $initializers);

        $this->registerBindings($bindings);
    }

    /**
     * Register the given bindings with the container and prepare markers.
     *
     * @param array <Binding> $bindings
     */
    protected function registerBindings(array $bindings)
    {
        foreach ($bindings as $binding) {
            $this->bindings[(string)$binding] = $binding;

            foreach ($binding->getMarkers() as $marker) {
                $marker = get_class($marker);

                if (empty($this->markers[$marker])) {
                    $this->markers[$marker] = new \SplObjectStorage();
                }

                $this->markers[$marker]->attach($binding);
            }
        }
    }

    public function __debugInfo()
    {
        return array_merge(parent::__debugInfo(), [
            'bindingCount' => count($this->bindings)
        ]);
    }

    /**
     * Clears the DI container by eliminating all contextual instances of bound objects.
     */
    public function clear()
    {
        foreach ($this->scopes as $scope) {
            $scope->clear();
        }
    }

    /**
     * Get all bindings registered with the container.
     *
     * @return array<string, Binding>
     */
    public function getBindings()
    {
        return $this->bindings;
    }

    /**
     * {@inheritdoc}
     */
    public function getBinding($name)
    {
        if (isset($this->bindings[$name])) {
            return $this->bindings[$name];
        }

        throw new TypeNotBoundException(sprintf('No such binding registered: "%s"', $name));
    }

    /**
     * {@inheritdoc}
     */
    public function eachMarked(callable $callback)
    {
        $ref = new \ReflectionFunction($callback);
        $type = $ref->getParameters()[0]->getClass();

        if (empty($this->markers[$type->name])) {
            return [];
        }

        $result = [];

        foreach ($this->markers[$type->name] as $binding) {
            foreach ($binding->getMarkers() as $marker) {
                if ($type->isInstance($marker)) {
                    $result[] = $callback($marker, $binding);
                }
            }
        }

        return $result;
    }

    /**
     * Create an instance of the bound type, will NOT create or re-use a scoped proxy.
     *
     * @param Binding $binding
     * @param InjectionPointInterface $point
     * @return object
     */
    public function createInstance(Binding $binding, InjectionPointInterface $point = NULL)
    {
        switch ($binding->getOptions() & BindingInterface::MASK_TYPE) {
            case BindingInterface::TYPE_ALIAS:
                return $this->get($binding->getTarget(), $point);
            case BindingInterface::TYPE_FACTORY:
            case BindingInterface::TYPE_FACTORY_ALIAS:
                return $this->createObjectUsingFactory($binding, $point);
            default:
                return $this->createObject(
                    $binding->getTarget(),
                    $binding->getResolvers(),
                    $binding->getInitializers(),
                    $binding->getMarkers(SetterInjection::class)
                );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function get($typeName, InjectionPointInterface $point = NULL)
    {
        if ($typeName === Configuration::class) {
            if ($point === NULL) {
                return $this->config;
            }

            return $this->config->getConfig(str_replace('\\', '.', $point->getTypeName()));
        }

        if (isset($this->proxies[$typeName])) {
            return $this->proxies[$typeName];
        }

        if (isset($this->bindings[$typeName])) {
            return $this->getBound($this->bindings[$typeName], $point);
        }

        return $this->createObject($typeName);
    }

    /**
     * {@inheritdoc}
     */
    public function getBound(BindingInterface $binding, InjectionPointInterface $point = NULL)
    {
        $scope = $binding->getScope();

        if ($scope === NULL) {
            switch ($binding->getOptions() & BindingInterface::MASK_TYPE) {
                case BindingInterface::TYPE_ALIAS:
                    return $this->get($binding->getTarget(), $point);
                case BindingInterface::TYPE_FACTORY:
                case BindingInterface::TYPE_FACTORY_ALIAS:
                    return $this->createObjectUsingFactory($binding, $point);
                default:
                    return $this->createObject(
                        $binding->getTarget(),
                        $binding->getResolvers(),
                        $binding->getInitializers(),
                        $binding->getMarkers(SetterInjection::class)
                    );
            }
        }

        $name = $binding->getTypeName();

        if (isset($this->proxies[$name])) {
            return $this->proxies[$name];
        }

        if (empty($this->scopes[$scope])) {
            throw new ScopeNotFoundException(sprintf('Scope %s of %s not found', $scope, $name));
        }

        if ($this->scopes[$scope] instanceof ProxyScopeManagerInterface) {
            return $this->proxies[$name] = $this->scopes[$scope]->createProxy($binding, $this);
        }

        switch ($binding->getOptions() & BindingInterface::MASK_TYPE) {
            case BindingInterface::TYPE_FACTORY:
            case BindingInterface::TYPE_FACTORY_ALIAS:
                return $this->proxies[$name] = $this->scopes[$scope]->lookup($name, function () use ($binding) {
                    return $this->createObjectUsingFactory($binding);
                });
            default:
                return $this->proxies[$name] = $this->scopes[$scope]->lookup($name, function () use ($binding) {
                    return $this->createObject(
                        $binding->getTarget(),
                        $binding->getResolvers(),
                        $binding->getInitializers(),
                        $binding->getMarkers(SetterInjection::class)
                    );
                });
        }
    }

    /**
     * Create an object using the factory defined in the given binding.
     *
     * @param Binding $binding
     * @param InjectionPointInterface $point
     * @return object
     */
    protected function createObjectUsingFactory(Binding $binding, InjectionPointInterface $point = NULL)
    {
        $callback = $binding->getTarget();

        if (is_array($callback)) {
            $callback = [$this->get($callback[0]), $callback[1]];
            $ref = new \ReflectionMethod(get_class($callback[0]), $callback[1]);
        } else {
            $ref = new \ReflectionFunction($callback);
        }

        $resolvers = (array)$binding->getResolvers();

        // Special case: inject config of the type to be created into the factory:
        foreach ($ref->getParameters() as $param) {
            if ($this->getParamType($param) === Configuration::class) {
                $resolvers[$param->name] = $this->config->getConfig(str_replace('\\', '.', $binding->getTypeName()));
            }
        }

        $args = $this->populateArguments($ref, 0, $resolvers, $point);

        switch (count($args)) {
            case 0:
                $object = $callback();
                break;
            case 1:
                $object = $callback($args[0]);
                break;
            case 2:
                $object = $callback($args[0], $args[1]);
                break;
            case 3:
                $object = $callback($args[0], $args[1], $args[2]);
                break;
            case 4:
                $object = $callback($args[0], $args[1], $args[2], $args[3]);
                break;
            default:
                $object = call_user_func_array($callback, $args);
        }

        if ($object instanceof ScopedProxyInterface) {
            return $object;
        }

        $object = $this->initialize($object);

        foreach ($binding->getMarkers(SetterInjection::class) as $setter) {
            $this->performSetterInjection($object, $setter);
        }

        $initializers = $binding->getInitializers();

        if (!empty($initializers)) {
            $object = $this->invokeBindingInitializers($binding->getTypeName(), $object, $initializers);
        }

        return $object;
    }

    /**
     * Get the application scope handler.
     *
     * @return ApplicationScopeManager
     */
    public function getApplicationScope()
    {
        if (empty($this->scopes[ApplicationScoped::class])) {
            throw new ScopeNotFoundException('Application scope not registered');
        }

        return $this->scopes[ApplicationScoped::class];
    }
}
