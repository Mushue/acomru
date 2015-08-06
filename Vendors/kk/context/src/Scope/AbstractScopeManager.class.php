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

use KoolKode\Context\Bind\BindingInterface;
use KoolKode\Context\Bind\DelegateBinding;
use KoolKode\Context\TypeNotBoundException;
use KoolKode\Context\TypeNotFoundException;

/**
 * Implements all the building blocks for a proxy-based scope manager implementation.
 *
 * @author Martin Schröder
 */
abstract class AbstractScopeManager implements \Serializable, ProxyScopeManagerInterface
{
    /**
     * Keeps track of all scoped proxies.
     *
     * @var \SplObjectStorage<BindingInterface, ScopedProxyInterface>
     */
    protected $scopedProxies;

    /**
     * Maps scoped proxies to their bound objects.
     *
     * @var \SplObjectStorage<ScopedProxyInterface, object>
     */
    protected $proxies;

    /**
     * The context object this scope is bound to (or NULL when the scope is not active).
     *
     * @var object
     */
    protected $context;

    /**
     * Keeps track of proxies in bound context objects.
     *
     * @var \SplObjectStorage<object, \SplObjectStorage<ScopedProxyInterface, object>>
     */
    protected $contextualInstances;

    /**
     * Provides access to the DI container being used.
     *
     * @var ScopedContainerInterface
     */
    protected $container;

    /**
     * Create storage objects needed by the scope.
     */
    public function __construct()
    {
        $this->scopedProxies = new \SplObjectStorage();
        $this->contextualInstances = new \SplObjectStorage();
        $this->proxies = new \SplObjectStorage();
    }

    public function __clone()
    {
        $this->container = NULL;
        $this->context = NULL;
        $this->contextualInstances = new \SplObjectStorage();
        $this->proxies = new \SplObjectStorage();
        $this->scopedProxies = new \SplObjectStorage();
    }

    /**
     * @codeCoverageIgnore
     */
    public function __debugInfo()
    {
        $data = [
            'scope' => $this->getScope(),
            'active' => $this->isActive(),
            'instances' => $this->contextualInstances->count()
        ];

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function isActive()
    {
        return $this->context !== NULL;
    }

    /**
     * Will throw an exception to prevent serialization.
     *
     * @throws \RuntimeException
     */
    public function serialize()
    {
        throw new \RuntimeException(sprintf('Scope %s must not serialized', $this->getScope()));
    }

    /**
     * Will throw an exception to prevent unserialization.
     *
     * @codeCoverageIgnore
     */
    public function unserialize($serialized)
    {
        throw new \RuntimeException(sprintf('Scope %s must not be unserialized', $this->getScope()));
    }

    /**
     * {@inheritdoc}
     */
    public function correlate(ScopedContainerInterface $container)
    {
        $this->container = $container;
        $this->container->bindInstance(get_class($this), $this, true);
    }

    /**
     * {@inheritdoc}
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * {@inheritdoc}
     */
    public function getProxyTypeNames()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        $this->context = NULL;
        $this->proxies = new \SplObjectStorage();
        $this->contextualInstances = new \SplObjectStorage();
        $this->scopedProxies = new \SplObjectStorage();
    }

    /**
     * {@inheritdoc}
     */
    public function lookup($typeName, callable $factory)
    {
        throw new \BadMethodCallException(sprintf('Proxy scopes do not support lookup(), use creaeProxy() instead'));
    }

    /**
     * {@inheritdoc}
     */
    public function createProxy(BindingInterface $binding)
    {
        if ($this->scopedProxies->offsetExists($binding)) {
            return $this->scopedProxies[$binding];
        }

        $typeName = $binding->getTypeName();

        if (!class_exists($typeName) && !interface_exists($typeName, false)) {
            throw new TypeNotFoundException(sprintf('Type %s not found', $typeName));
        }

        $proxyName = $typeName . '__scoped';

        if (!class_exists($proxyName, false)) {
            $proxyName = $this->container->loadScopedProxy($typeName);
        }

        $proxy = new $proxyName($binding, $this, $this->proxies);
        $this->scopedProxies[$binding] = $proxy;

        return $proxy;
    }

    /**
     * {@inheritdoc}
     */
    public function activateInstance(ScopedProxyInterface $proxy)
    {
        if ($this->context === NULL) {
            throw new ScopeNotActiveException(sprintf('Scope "%s" is not active', $this->getScope()));
        }

        if (!$this->scopedProxies->contains($proxy->K2GetProxyBinding())) {
            $this->scopedProxies[$proxy->K2GetProxyBinding()] = $proxy;
        }

        if (!$this->proxies->contains($proxy)) {
            $target = $proxy->K2GetProxyBinding()->__invoke($this->container);
            $this->proxies->attach($proxy, $target);
        }

        return $target;
    }

    /**
     * Bind the scope to the given object.
     *
     * @param object $object
     * @return object The previous context or NULL when the scope was not active.
     */
    protected function bindContext($object = NULL)
    {
        if ($object !== NULL && !is_object($object)) {
            throw new \InvalidArgumentException(sprintf('Expecting an object, given %s', gettype($object)));
        }

        $previous = $this->context;
        $this->context = $object;

        if ($object === NULL) {
            $this->proxies = new \SplObjectStorage();

            return $previous;
        }

        if ($this->contextualInstances->offsetExists($object)) {
            $this->proxies = $this->contextualInstances[$object];
        } else {
            $this->proxies = new \SplObjectStorage();
            $this->contextualInstances[$object] = $this->proxies;
        }

        return $previous;
    }

    /**
     * Unbind scope from the given object, will optionally destroy all contextual instances bound to the object.
     *
     * @param object $object
     * @param boolean $terminate Clear all contextual instances?
     *
     * @throws \InvalidArgumentException
     */
    protected function unbindContext($object, $terminate = true)
    {
        if (!is_object($object)) {
            throw new \InvalidArgumentException(sprintf('Expecting an object, given %s', gettype($object)));
        }

        if ($this->context === $object) {
            $this->context = NULL;
            $this->proxies = new \SplObjectStorage();
        }

        if ($terminate && $this->contextualInstances->offsetExists($object)) {
            unset($this->contextualInstances[$object]);
        }
    }

    /**
     * Creates a factory-based proxy for the target type and binds it to the DI container.
     *
     * @param string $typeName
     * @param callable $factory
     * @return ScopedProxyInterface
     */
    protected function bindFactoryProxy($typeName, callable $factory)
    {
        $proxy = $this->initializeProxy($typeName, $factory);

        $this->scopedProxies[$proxy->K2GetProxyBinding()] = $proxy;
        $this->container->bindInstance($typeName, $proxy, true);

        return $proxy;
    }

    /**
     * Create a scoped proxy for the given type.
     *
     * @param string $typeName
     * @param callable $factory Will be called when the proxy is activated.
     * @return ScopedProxyInterface
     */
    protected function initializeProxy($typeName, callable $factory = NULL)
    {
        $proxyName = $this->container->loadScopedProxy($typeName);

        if ($factory === NULL) {
            $factory = function () {
                throw new ScopeNotActiveException(sprintf('Scope "%s" is not active', $this->getScope()));
            };
        }

        return new $proxyName(new DelegateBinding($typeName, $this->getScope(), 0, $factory), $this, $this->proxies);
    }
}
