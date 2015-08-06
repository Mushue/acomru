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

use KoolKode\Context\ContainerInterface;
use KoolKode\Context\InjectionPointInterface;
use KoolKode\Context\Scope\Dependent;
use KoolKode\Context\Scope\Scope;

/**
 * Builder for an object configuration to be used in the DI container.
 *
 * @author Martin Schröder
 */
final class Binding extends AbstractBinding
{
    /**
     * Target of the binding eighter a string (alias name, type name) or a
     * closure (factory callback).
     *
     * @var mixed
     */
    protected $to;

    /**
     * Holds all markers being set for this binding.
     *
     * @var array<Marker>
     */
    protected $markers;

    /**
     * Contains all resolvers for named constructor arguments.
     *
     * @var array<string, mixed>
     */
    protected $resolvers;

    /**
     * Contains all specialized initializers of this binding.
     *
     * @var array<\Closure>
     */
    protected $initializers;

    /**
     * Contains all registered decorators sorted by priority in descending order.
     *
     * @var \SplPriorityQueue<\Closure>
     */
    protected $decorators;

    /**
     * Create a new (implmentation) binding for the given type name.
     *
     * @param string $typeName
     */
    public function __construct($typeName)
    {
        $this->typeName = ($typeName instanceof \ReflectionClass) ? $typeName->name : (string)$typeName;
        $this->options = self::TYPE_IMPLEMENTATION;

        $this->decorators = new \SplPriorityQueue();
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(ContainerInterface $container, InjectionPointInterface $point = NULL)
    {
        return $container->createInstance($this, $point);
    }

    public function __debugInfo()
    {
        $data = get_object_vars($this);

        unset($data['initializers'], $data['decorators']);

        $data['markers'] = (array)$data['markers'];

        if ($data['to'] instanceof \Closure) {
            $data['to'] = '*closure*';
        }

        $data['resolvers'] = array_keys((array)$this->resolvers);
        $data['initializerCount'] = count((array)$this->initializers);
        $data['decoratorCount'] = $this->decorators->count();

        return $data;
    }

    /**
     * Get the target of this binding, eighter a string or a closure.
     *
     * @return mixed
     */
    public function getTarget()
    {
        if ($this->to === NULL && $this->options | self::TYPE_IMPLEMENTATION) {
            return $this->typeName;
        }

        return $this->to;
    }

    /**
     * Get all markers being set for this binding.
     *
     * @param string $marker The type of marker to be considerd.
     * @return array<Marker>
     */
    public function getMarkers($marker = NULL)
    {
        if ($marker !== NULL && $this->markers !== NULL) {
            $result = [];

            foreach ($this->markers as $check) {
                if ($check instanceof $marker) {
                    $result[] = $check;
                }
            }

            return $result;
        }

        return (array)$this->markers;
    }

    /**
     * Bind to an implementation (fully-qualified class name as a string) or to a factory provided by eighter
     * a closure or an instance method on a bound object (does not require an explicit binding tough).
     *
     * @param mixed $target The target instance or factory closure.
     * @param string $methodName The factory method to be used with an instance name.
     * @return Binding
     *
     * @throws \InvalidArgumentException When the target is neighter FQN nor factory callback.
     */
    public function to($target, $methodName = NULL)
    {
        if (is_string($target)) {
            if ($methodName === NULL) {
                $this->options = ($this->options | self::MASK_TYPE) ^ self::MASK_TYPE | self::TYPE_IMPLEMENTATION;
                $this->to = ($target === $this->typeName) ? NULL : $target;
            } else {
                $this->options = ($this->options | self::MASK_TYPE) ^ self::MASK_TYPE | self::TYPE_FACTORY_ALIAS;
                $this->to = [$target, (string)$methodName];
            }
        } elseif ($target instanceof \Closure) {
            if ($methodName !== NULL) {
                throw new \InvalidArgumentException('Must not specify a method name when binding a factory closure');
            }

            $this->options = ($this->options | self::MASK_TYPE) ^ self::MASK_TYPE | self::TYPE_FACTORY;
            $this->to = method_exists('Closure', 'bindTo') ? $target->bindTo(NULL, NULL) : $target;
        } else {
            $input = is_object($target) ? get_class($target) : '"' . gettype($target) . '"';

            throw new \InvalidArgumentException(sprintf('Expecting string or callback, given "%s"', $input));
        }

        return $this;
    }

    /**
     * Bind to another binding specified by name.
     *
     * @param string $typeName
     * @return Binding
     */
    public function toAlias($typeName)
    {
        $this->options = self::TYPE_ALIAS;
        $this->to = (string)$typeName;

        return $this;
    }

    /**
     * Set the scope of the binding according to the binary value of the scope.
     *
     * @param Scope $scope
     * @return Binding
     */
    public function scoped(Scope $scope)
    {
        if ($scope instanceof Dependent) {
            $this->scope = NULL;
        } else {
            $this->scope = get_class($scope);
        }

        return $this;
    }

    /**
     * Apply a marker (tag) to the binding.
     *
     * @param Marker $marker
     * @return Binding
     */
    public function marked(Marker $marker)
    {
        $this->markers[] = $marker;

        return $this;
    }

    /**
     * Check if the binding has a marker of the given type.
     *
     * @param mixed $marker Marker instance, reflection class or fully-qualified name of the marker.
     * @return boolean
     */
    public function isMarked($marker)
    {
        if (empty($this->markers)) {
            return false;
        }

        if ($marker instanceof \ReflectionClass) {
            foreach ($this->markers as $check) {
                if ($marker->isInstance($check)) {
                    return true;
                }
            }
        } elseif ($marker instanceof Marker) {
            foreach ($this->markers as $check) {
                if ($marker->isInstance($check)) {
                    return true;
                }
            }
        } else {
            foreach ($this->markers as $check) {
                if ($check instanceof $marker) {
                    return true;
                }
            }
        }

        return false;
    }

    public function getMarker($marker)
    {
        if ($this->markers !== NULL) {
            foreach ($this->markers as $check) {
                if ($check instanceof $marker) {
                    return $check;
                }
            }
        }

        if (func_num_args() > 1) {
            return func_get_arg(1);
        }

        throw new \OutOfBoundsException(sprintf('Binding %s is not marked with %s', $this->typeName, $marker));
    }

    /**
     * Register a resolver that populates the value of a constructor param
     * in the target type, passing a closure will behave like a factory-closure, passing anything
     * else will pass the given value to the constructor argument.
     *
     * The same behavior applies to factory binding where the resolver is used to when
     * factory params are populated.
     *
     * @param string $name The name of the constructor param to be resolved.
     * @param mixed $resolver The value / factory for the resolved param.
     * @return Binding
     */
    public function resolve($name, $resolver)
    {
        $this->resolvers[$name] = $resolver;

        return $this;
    }

    /**
     * Get all constructor param resolvers.
     *
     * @return array<string, mixed>
     */
    public function getResolvers()
    {
        return $this->resolvers;
    }

    /**
     * Register an initializer to be called after object creation, initializers receive the
     * created object instance as first argument and may declare any number of additional
     * arguments that will be resolved by the DI container.
     *
     * Initializers can also be used with factory bindings.
     *
     * @param \Closure $initializer
     * @return Binding
     */
    public function initialize(\Closure $initializer)
    {
        $this->initializers[] = $initializer;

        return $this;
    }

    /**
     * Register a decorator with this binding.
     *
     * Decorators are custom initializers (created object instance as first argument + any number of additional
     * params reslved by DI are supported) that must return the decorator object instance.
     *
     * @param \Closure $decorator
     * @param integer $priority
     * @return Binding
     */
    public function decorate(\Closure $decorator, $priority = 0)
    {
        $this->decorators->insert($decorator, $priority);

        return $this;
    }

    /**
     * Get all custom initializers of this binding.
     *
     * @return array<\Closure>
     */
    public function getInitializers()
    {
        $initializers = $this->initializers;

        foreach (clone $this->decorators as $decorator) {
            $initializers[] = $decorator;
        }

        return $initializers;
    }
}
