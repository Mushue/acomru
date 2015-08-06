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
use KoolKode\Context\Bind\ContainerInitializerInterface;
use KoolKode\Context\Bind\SetterInjection;
use KoolKode\Context\Scope\DuplicateScopeException;
use KoolKode\Context\Scope\ScopedContainerInterface;
use KoolKode\Context\Scope\ScopedProxyException;
use KoolKode\Context\Scope\ScopedProxyGenerator;
use KoolKode\Context\Scope\ScopedProxyInterface;
use KoolKode\Context\Scope\ScopeManagerInterface;
use KoolKode\Context\Scope\ScopeNotFoundException;
use KoolKode\Context\Scope\Singleton;
use KoolKode\Context\Scope\SingletonScopeManager;
use KoolKode\Util\ReflectionTrait;

/**
 * Provides a shared base for DI container implementations.
 *
 * @author Martin Schröder
 */
abstract class AbstractContainer implements \Serializable, ExposedContainerInterface, ScopedContainerInterface
{
    use ReflectionTrait;

    /**
     * Keeps track of types that have been proxied (needed by unit tests).
     *
     * @var array<string, boolean>
     */
    private static $proxyTypes = [];
    /**
     * DI container parameters to be used by other components.
     *
     * @var array<string, mixed>
     */
    protected $parameters;
    /**
     * Contains all registered initializers to be considered during object instantiation.
     *
     * @var array<ContainerInitializerInterface>
     */
    protected $initializers;
    /**
     * The system configuration to be provided to managed objects.
     *
     * @var Configuration
     */
    protected $config;
    /**
     * Holds all registered scope managers keyed to their scope marker names.
     *
     * @var array<string, ScopeManagerInterface>
     */
    protected $scopes = [];
    /**
     * Maps binding names to scoped proxies / bound object instances.
     *
     * @var array<string, object>
     */
    protected $proxies = [];
    /**
     * Tracks types that are under construction in order to prevent problems when
     * creating cyclic constructor dependencies.
     *
     * @var array<string, boolean>
     */
    protected $underConstruction = [];
    /**
     * Scoped proxy generator to be used.
     *
     * @var ScopedProxyGenerator
     */
    protected $proxyGenerator;
    /**
     * Caches setter injection definitions grouped by type name.
     *
     * @var \SplObjectStorage<SetterInjection, array<string, array<string>>>
     */
    private $setterCache;

    /**
     * Creates the DI container and registers the application scope.
     *
     * @param array <string, mixed> $parameters
     * @param array <ContainerInitializerInterface> $initializers
     */
    public function __construct(array $parameters = [], array $initializers = [])
    {
        $this->parameters = $parameters;
        $this->initializers = $initializers;

        $this->config = new Configuration();
        $this->proxyGenerator = new ScopedProxyGenerator();

        $this->proxies[ContainerInterface::class] = $this;
        $this->proxies[ExposedContainerInterface::class] = $this;
        $this->proxies[ScopedContainerInterface::class] = $this;

        $this->registerScope(new SingletonScopeManager());
    }

    /**
     * Register and correalate a scope with this DI container instance.
     *
     * @param ScopeManagerInterface $scope The scope to be registered.
     * @return ScopeManagerInterface The registered scope.
     *
     * @throws DuplicateScopeException When the scope is already registered with the container.
     */
    public function registerScope(ScopeManagerInterface $scope)
    {
        if (isset($this->scopes[$scope->getScope()])) {
            throw new DuplicateScopeException(sprintf('Scope "%s" is already registered', $scope->getScope()));
        }

        $this->scopes[$scope->getScope()] = $scope;

        $scope->correlate($this);

        return $scope;
    }

    public function __clone()
    {
        $this->proxies = [
            ContainerInterface::class => $this,
            ExposedContainerInterface::class => $this,
            ScopedContainerInterface::class => $this
        ];

        foreach ($this->scopes as $name => $scope) {
            $scope = clone $scope;

            $this->scopes[$name] = $scope;
            $this->proxies[$name] = $scope;

            $scope->correlate($this);
        }

        $this->underConstruction = [];
    }

    public function __debugInfo()
    {
        return [
            'parameters' => $this->parameters,
            'initializers' => $this->initializers,
            'scopes' => $this->scopes,
            'proxies' => array_keys($this->proxies)
        ];
    }

    /**
     * Safe guard in order to prevent accidental serialization of the container.
     *
     * @throws \RuntimeException
     */
    public function serialize()
    {
        throw new \RuntimeException(sprintf('%s must not be serialized', get_class($this)));
    }

    /**
     * @codeCoverageIgnore
     */
    public function unserialize($serialized)
    {
        throw new \RuntimeException(sprintf('%s must not be unserialized', get_class($this)));
    }

    /**
     * Initialize the object by injection the DI container into aware objects and invoking
     * all container initializers passing the target object.
     *
     * @param object $object The object to be initialized.
     * @return object The object instance that has been passed into the initializer.
     */
    public function initialize($object)
    {
        if ($object instanceof ScopedProxyInterface) {
            return $object;
        }

        if ($object instanceof ContainerAwareInterface) {
            $object->setContainer($this);
        }

        foreach ($this->initializers as $initializer) {
            $object = $initializer->initializeObject($object, $this) ?: $object;
        }

        return $object;
    }

    /**
     * {@inheritdoc}
     */
    public function hasParameter($name)
    {
        return array_key_exists($name, $this->parameters);
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * Get all initializers registered with the container.
     *
     * @return array<ContainerInitializerInterface>
     */
    public function getInitializers()
    {
        return $this->initializers;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfiguration()
    {
        return $this->config;
    }

    /**
     * Set or change the configuration provided by the DI container.
     *
     * @param Configuration $config
     */
    public function setConfiguration(Configuration $config)
    {
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function getScope($scope)
    {
        if (empty($this->scopes[$scope])) {
            throw new ScopeNotFoundException(sprintf('Scope %s not found', $scope));
        }

        return $this->scopes[$scope];
    }

    /**
     * {@inheritdoc}
     */
    public function bindInstance($typeName, $instance, $local = false)
    {
        switch ($typeName) {
            case Configuration::class:
            case ContainerInterface::class:
            case ExposedContainerInterface::class:
            case ScopedContainerInterface::class:
                throw new \InvalidArgumentException(sprintf('Binding %s is forbidden', $typeName));
        }

        if ($local) {
            return $this->proxies[$typeName] = $instance;
        }

        return $this->proxies[$typeName] = $this->scopes[Singleton::class]->register($typeName, $instance);
    }

    /**
     * {@inheritdoc}
     */
    public function isProxy($object)
    {
        return in_array($object, $this->proxies, true);
    }

    /**
     * {@inheritdoc}
     */
    public function getBoundTypeOfProxy($object)
    {
        $type = array_search($object, $this->proxies, true);

        return ($type === false) ? NULL : $type;
    }

    /**
     * {@inheritdoc}
     */
    public function hasProxy($typeName)
    {
        return isset($this->proxies[$typeName]);
    }

    /**
     * Attempts to create an instance of the given type using reflection of the constructor
     * and auto-injection of all required dependencies.
     *
     * @param string $typeName The fully-qualified class name of the type.
     * @param array <string, mixed> $resolvers Resolvers for named constructor params.
     * @param array <callable> $initializers Object initializers to be called after object creation.
     * @param mixed $setterInjection Setter injection closure or array of marker for reflection-based injection.
     * @return object The created object instance.
     *
     * @throws TypeNotFoundException Is thrown when the given type could not be loaded.
     * @throws ContextLookupException Is thrown when the target type is not instantiable.
     */
    public function createObject($typeName, array $resolvers = NULL, array $initializers = NULL, $setterInjection = NULL)
    {
        try {
            $ref = new \ReflectionClass($typeName);

            if (!$ref->isInstantiable()) {
                throw new ContextLookupException(sprintf('Cannot create an instance of abstract type: %s', $ref->name));
            }
        } catch (\ReflectionException $e) {
            throw new TypeNotFoundException(sprintf('Type not found: %s', $typeName), 0, $e);
        }

        if (isset($this->underConstruction[$ref->name])) {
            $cycle = array_keys($this->underConstruction);
            $cycle[] = $typeName;

            throw new ContextLookupException(sprintf('Cyclic constructor dependency detected during creation of %s, cycle is %s', $ref->name, implode(' -> ', $cycle)));
        }

        $this->underConstruction[$ref->name] = true;

        try {
            if ($ref->hasMethod('__construct')) {
                $args = $this->populateArguments($ref->getConstructor(), 0, $resolvers, new InjectionPoint($ref->name, '__construct'));

                switch (count($args)) {
                    case 0:
                        $object = new $typeName();
                        break;
                    case 1:
                        $object = new $typeName($args[0]);
                        break;
                    case 2:
                        $object = new $typeName($args[0], $args[1]);
                        break;
                    case 3:
                        $object = new $typeName($args[0], $args[1], $args[2]);
                        break;
                    case 4:
                        $object = new $typeName($args[0], $args[1], $args[2], $args[3]);
                        break;
                    default:
                        $object = $ref->newInstanceArgs($args);
                }
            } else {
                $object = new $typeName();
            }
        } finally {
            unset($this->underConstruction[$ref->name]);
        }

        if ($object instanceof ContainerAwareInterface) {
            $object->setContainer($this);
        }

        foreach ($this->initializers as $initializer) {
            $object = $initializer->initializeObject($object, $this) ?: $object;
        }

        if ($setterInjection instanceof \Closure) {
            $setterInjection($object);
        } elseif (is_array($setterInjection)) {
            foreach ($setterInjection as $setter) {
                $this->performSetterInjection($object, $setter);
            }
        }

        if (!empty($initializers)) {
            $object = $this->invokeBindingInitializers($typeName, $object, $initializers);
        }

        return $object;
    }

    /**
     * {@inheritdoc}
     */
    public function populateArguments(\ReflectionFunctionAbstract $ref, $skip = 0, array $resolvers = NULL, InjectionPointInterface $point = NULL)
    {
        $args = [];

        foreach ($ref->getParameters() as $param) {
            if ($skip > 0) {
                $skip--;

                continue;
            }

            if (isset($resolvers[$param->name])) {
                if (!$resolvers[$param->name] instanceof \Closure) {
                    $args[] = $resolvers[$param->name];

                    continue;
                }

                $tmp = $this->populateArguments(new \ReflectionFunction($resolvers[$param->name]), 0, NULL, $point);

                switch (count($tmp)) {
                    case 0:
                        $args[] = $resolvers[$param->name]();
                        break;
                    case 1:
                        $args[] = $resolvers[$param->name]($tmp[0]);
                        break;
                    case 2:
                        $args[] = $resolvers[$param->name]($tmp[0], $tmp[1]);
                        break;
                    case 3:
                        $args[] = $resolvers[$param->name]($tmp[0], $tmp[1], $tmp[2]);
                        break;
                    case 4:
                        $args[] = $resolvers[$param->name]($tmp[0], $tmp[1], $tmp[2], $tmp[3]);
                        break;
                    default:
                        $args[] = call_user_func_array($resolvers[$param->name], $tmp);
                }

                continue;
            }

            if (NULL === ($type = $this->getParamType($param))) {
                if ($param->isOptional()) {
                    $args[] = $param->isDefaultValueAvailable() ? $param->getDefaultValue() : NULL;

                    continue;
                }

                throw new ContextLookupException(sprintf('Cannot populate non-object param "%s" without resolver or default value in %s', $param->name, $this->getCallableName($ref)));
            }

            if ($type == InjectionPointInterface::class) {
                if ($point === NULL && !$param->isOptional()) {
                    throw new ContextLookupException(sprintf('%s requires access to an injection point', $this->getCallableName($ref)));
                }

                $args[] = $point;

                continue;
            }

            $e = NULL;

            try {
                $args[] = $this->get($type, $point);

                continue;
            } catch (ContextLookupException $e) {
            } catch (TypeNotFoundException $e) {
            }

            if ($param->isOptional()) {
                $args[] = $param->isDefaultValueAvailable() ? $param->getDefaultValue() : NULL;

                continue;
            }

            throw new ContextLookupException(sprintf('Unable to populate parameter "%s" using type-hint %s in %s', $param->getName(), $type, $this->getCallableName($ref)), 0, $e);
        }

        return $args;
    }

    /**
     * Get a human-readable name from a reflected callable.
     *
     * @param \ReflectionFunctionAbstract $ref
     * @return string
     */
    protected function getCallableName(\ReflectionFunctionAbstract $ref)
    {
        if ($ref instanceof \ReflectionMethod) {
            return $ref->getDeclaringClass()->name . '->' . $ref->name . '()';
        }

        if ($ref->isClosure()) {
            return '*closure*';
        }

        return $ref->getName() . '()';
    }

    /**
     * Collects required injection methods using reflection and calls them with
     * arguments pulled from the container.
     *
     * @param object $object
     * @param SetterInjection $setter
     * @return object
     *
     * @throws \RuntimeException When an injection method does not declare params or is missing appropriate type-hints on params.
     */
    public function performSetterInjection($object, SetterInjection $setter)
    {
        if ($this->setterCache === NULL) {
            $this->setterCache = new \SplObjectStorage();
        }

        if (!$this->setterCache->contains($setter)) {
            $this->setterCache[$setter] = [];

            $injects = [];
            $candidates = [];

            if ($setter->isConvention()) {
                foreach (get_class_methods($object) as $methodName) {
                    if ('inject' !== strtolower(substr($methodName, 0, 6))) {
                        continue;
                    }

                    $candidates[] = $methodName;
                }
            } elseif ($setter->isFilter()) {
                foreach (get_class_methods($object) as $methodName) {
                    if ('set' !== strtolower(substr($methodName, 0, 3))) {
                        continue;
                    }

                    $candidates[] = $methodName;
                }
            } else {
                foreach (get_class_methods($object) as $methodName) {
                    $candidates[] = $methodName;
                }
            }

            foreach ($candidates as $methodName) {
                $ref = new \ReflectionMethod($object, $methodName);

                if ($ref->isStatic() || $ref->isAbstract() || !$ref->isPublic()) {
                    continue;
                }

                $params = $ref->getParameters();

                if (empty($params)) {
                    continue;
                }

                $types = [];

                foreach ($params as $param) {
                    if (NULL === ($paramTypeName = $this->getParamType($param))) {
                        continue 2;
                    }

                    $types[] = [$paramTypeName, $param->isOptional() ? ($param->isDefaultValueAvailable() ? $param->getDefaultValue() : NULL) : false];
                }

                if ($setter->accept($ref)) {
                    $injects[$methodName] = $types;
                }
            }

            $this->setterCache[$setter] = $injects;
        }

        foreach ($this->setterCache[$setter] as $methodName => $types) {
            $key = get_class($object);
            $args = [];

            foreach ($types as $typeDef) {
                if ($typeDef[1] === false) {
                    $args[] = $this->get($typeDef[0], new InjectionPoint($key, $methodName));
                } else {
                    try {
                        $args[] = $this->get($typeDef[0], new InjectionPoint($key, $methodName));
                    } catch (ContextLookupException $e) {
                        $args[] = $typeDef[1];
                    }
                }
            }

            switch (count($args)) {
                case 1:
                    $object->$methodName($args[0]);
                    break;
                case 2:
                    $object->$methodName($args[0], $args[1]);
                    break;
                case 3:
                    $object->$methodName($args[0], $args[1], $args[2]);
                    break;
                case 4:
                    $object->$methodName($args[0], $args[1], $args[2], $args[3]);
                    break;
                default:
                    call_user_func_array([$object, $methodName], $args);
            }
        }

        return $object;
    }

    /**
     * Invoke all passed initializers for the given object.
     *
     * @param object $object The object to be initialized.
     * @param string $initializers Initializers to be called.
     * @return object Returns the passed object instance.
     */
    protected function invokeBindingInitializers($typeName, $object, $initializers = NULL)
    {
        foreach ((array)$initializers as $initializer) {
            if (is_array($initializer)) {
                $ref = new \ReflectionMethod(is_object($initializer[0]) ? get_class($initializer[0]) : $initializer[0], $initializer[1]);
            } else {
                $ref = new \ReflectionFunction($initializer);
            }

            $args = $this->populateArguments($ref, 1, [], new InjectionPoint($typeName, $ref->name));

            switch (count($args)) {
                case 0:
                    $object = $initializer($object) ?: $object;
                    break;
                case 1:
                    $object = $initializer($object, $args[0]) ?: $object;
                    break;
                case 2:
                    $object = $initializer($object, $args[0], $args[1]) ?: $object;
                    break;
                case 3:
                    $object = $initializer($object, $args[0], $args[1], $args[2]) ?: $object;
                    break;
                case 4:
                    $object = $initializer($object, $args[0], $args[1], $args[2], $args[3]) ?: $object;
                    break;
                default:
                    $object = call_user_func_array($initializer, array_merge([$object], $args)) ?: $object;
            }
        }

        return $object;
    }

    /**
     * {@inheritdoc}
     */
    public function loadScopedProxy($typeName)
    {
        $ref = new \ReflectionClass($typeName);

        if ($ref->isTrait()) {
            throw new ScopedProxyException(sprintf('Cannot generate scoped proxy for trait %s', $ref->name));
        }

        $proxyName = $ref->name . '__scoped';

        if (class_exists($proxyName, false) || isset(self::$proxyTypes[strtolower($proxyName)])) {
            return $proxyName;
        }

        $code = $this->proxyGenerator->generateProxyCode($ref);

        eval($code);

        self::$proxyTypes[strtolower($proxyName)] = true;

        return $proxyName;
    }
}
