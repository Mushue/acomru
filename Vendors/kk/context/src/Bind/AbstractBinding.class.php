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
use KoolKode\Context\Scope\Scope;

/**
 * Base class for custom binding implementations.
 *
 * @author Martin Schröder
 */
abstract class AbstractBinding implements BindingInterface
{
    /**
     * The fully-qualified name of the bound type.
     *
     * @var string
     */
    protected $typeName;

    /**
     * The scope of the binding (FQN of the scope marker) or NULL when scope is dependent.
     *
     * @var string
     */
    protected $scope;

    /**
     * Binding options combined into an integer.
     *
     * @var integer
     */
    protected $options;

    /**
     * Resolve this binding to an instance (will not use scoped proxies!).
     *
     * @param ContainerInterface $container
     * @param InjectionPointInterface $point
     * @return object
     */
    public abstract function __invoke(ContainerInterface $container, InjectionPointInterface $point = NULL);

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return $this->typeName;
    }

    /**
     * {@inheritdoc}
     */
    public function getTypeName()
    {
        return $this->typeName;
    }

    /**
     * {@inheritdoc}
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Checks if this binding uses a factory function.
     *
     * @return boolean
     */
    public function isFactoryBinding()
    {
        return ($this->options & self::TYPE_FACTORY) != 0;
    }

    /**
     * Checks if this binding uses a factory type.
     *
     * @return boolean
     */
    public function isFactoryAliasBinding()
    {
        return ($this->options & self::TYPE_FACTORY_ALIAS) != 0;
    }

    /**
     * Checks if this binding creates it's instances using a constructor.
     *
     * @return boolean
     */
    public function isImplementationBinding()
    {
        return ($this->options & self::TYPE_IMPLEMENTATION) != 0;
    }

    /**
     * Check if this binding is aliased to another binding.
     *
     * @return boolean
     */
    public function isAliasBinding()
    {
        return ($this->options & self::TYPE_ALIAS) != 0;
    }

    /**
     * Get the type of the binding (factory, impl, alias).
     *
     * @return integer
     */
    public function getType()
    {
        return $this->options & self::MASK_TYPE;
    }

    /**
     * {@inheritdoc}
     */
    public function getScope()
    {
        return $this->scope;
    }
}
