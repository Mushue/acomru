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
use KoolKode\Context\Bind\DelegateBinding;
use KoolKode\Context\Scope\ProxyScopeManagerInterface;
use KoolKode\Context\Scope\ScopedProxyInterface;
use KoolKode\Context\Scope\ScopeNotFoundException;

/**
 * Base for a compiled container that provides very fast access to bindings and bound instances.
 *
 * @author Martin Schröder
 */
abstract class CompiledContainer extends AbstractContainer
{
    protected $bound = [];

    public function __clone()
    {
        parent::__clone();

        // Clear or re-bind cached delegate bindings as they are scoped to the container.
        if (method_exists('Closure', 'bindTo')) {
            foreach ($this as $v) {
                if ($v instanceof DelegateBinding) {
                    $v->bindCallback($this);
                }
            }
        } else {
            foreach ($this as $k => $v) {
                if ($v instanceof DelegateBinding) {
                    $this->$k = NULL;
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getBinding($typeName)
    {
        if (isset($this->bound[$typeName])) {
            if ($this->bound[$typeName] === true) {
                return $this->bound[$typeName] = $this->{'binding_' . str_replace('\\', '_', $typeName)}();
            }

            return $this->bound[$typeName];
        }

        throw new TypeNotBoundException(sprintf('No such binding registered: "%s"', $typeName));
    }

    /**
     * {@inheritdoc}
     */
    public function eachMarked(callable $callback)
    {
        $ref = new \ReflectionFunction($callback);
        $type = $this->getParamType($ref->getParameters()[0]);

        $methodName = 'markedBindings_' . str_replace('\\', '_', $type);

        if (method_exists($this, $methodName)) {
            $result = [];

            foreach ($this->$methodName() as $data) {
                $result[] = $callback($data[0], $data[1]);
            }

            return $result;
        }

        return [];
    }

    protected function getNullable($typeName, InjectionPointInterface $point = NULL)
    {
        try {
            return $this->get($typeName, $point);
        } catch (ContextLookupException $e) {
            return NULL;
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

        if (isset($this->bound[$typeName])) {
            if ($this->bound[$typeName] === true) {
                return $this->getBound($this->bound[$typeName] = $this->{'binding_' . str_replace('\\', '_', $typeName)}(), $point);
            }

            return $this->getBound($this->bound[$typeName], $point);
        }

        return $this->createObject($typeName);
    }

    /**
     * {@inheritdoc}
     */
    public function getBound(BindingInterface $binding, InjectionPointInterface $point = NULL)
    {
        if (!$binding instanceof DelegateBinding) {
            return $this->get($binding->getTypeName(), $point);
        }

        $scope = $binding->getScope();

        if ($scope === NULL) {
            return $binding($this, $point);
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

        return $this->proxies[$name] = $this->scopes[$scope]->lookup($name, function () use ($binding) {
            return $binding($this);
        });
    }

    /**
     * Create an object instance by calling a factory method on an object.
     *
     * @param string $typeName The bound type name of the target type.
     * @param object $factory The factory instance to be used.
     * @param string $methodName The name of the factory method to be called.
     * @param array <string, mixed> $resolvers Param resolvers applied to factory method arguments.
     * @param InjectionPointInterface $point Injection point that receives the generated object.
     * @return object The created object instance.
     */
    protected function callFactoryMethod($typeName, $factory, $methodName, array $resolvers = NULL, InjectionPointInterface $point = NULL)
    {
        $ref = new \ReflectionMethod(get_class($factory), $methodName);

        foreach ($ref->getParameters() as $param) {
            if (Configuration::class === $this->getParamType($param)) {
                $resolvers[$param->name] = $this->config->getConfig(str_replace('\\', '.', $typeName));
            }
        }

        $args = $this->populateArguments($ref, 0, $resolvers, $point);

        switch (count($args)) {
            case 0:
                return $factory->$methodName();
            case 1:
                return $factory->$methodName($args[0]);
            case 2:
                return $factory->$methodName($args[0], $args[1]);
            case 3:
                return $factory->$methodName($args[0], $args[1], $args[2]);
            case 4:
                return $factory->$methodName($args[0], $args[1], $args[2], $args[3]);
        }

        return call_user_func_array([$factory, $methodName], $args);
    }

    /**
     * Revives a marker instance by creating it without constructor invocation.
     *
     * @param string $typeName
     * @param array <string, mixed> $params
     * @return Marker
     */
    protected function reviveMarker($typeName, array $params = NULL)
    {
        static $refs = [];

        if (empty($refs[$typeName])) {
            $refs[$typeName] = new \ReflectionClass($typeName);
        }

        $marker = $refs[$typeName]->newInstanceWithoutConstructor();

        foreach ((array)$params as $k => $v) {
            $marker->$k = $v;
        }

        return $marker;
    }
}
