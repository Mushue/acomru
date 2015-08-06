<?php

namespace KoolKode\Context\Scope;

/**
 * Scpecialized shared scope that does not require scoped proxies.
 *
 * @author Martin Schröder
 */
class SingletonScopeManager implements ScopeManagerInterface
{
    protected $instances = [];

    protected $container;

    public function __clone()
    {
        $this->container = NULL;
    }

    /**
     * @codeCoverageIgnore
     */
    public function __debugInfo()
    {
        return [
            'scope' => $this->getScope(),
            'instances' => empty($this->instances) ? 0 : count($this->instances)
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getScope()
    {
        return Singleton::class;
    }

    /**
     * {@inheritdoc}
     */
    public function isActive()
    {
        return $this->container !== NULL;
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
    public function correlate(ScopedContainerInterface $container)
    {
        $this->container = $container;
        $this->container->bindInstance(SingletonScopeManager::class, $this, true);
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        $this->instances = [];
    }

    /**
     * {@inheritdoc}
     */
    public function lookup($typeName, callable $factory)
    {
        if (isset($this->instances[$typeName])) {
            return $this->instances[$typeName];
        }

        return $this->instances[$typeName] = $factory($this);
    }

    /**
     * Register the given object with the singleton scope.
     *
     * @param string $typeName
     * @param object $instance
     * @return object The registered object instance.
     */
    public function register($typeName, $instance)
    {
        return $this->instances[(string)$typeName] = $instance;
    }
}
