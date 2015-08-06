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

/**
 * Binding implementation that creates an object instance using a closure.
 *
 * @author Martin Schröder
 */
class DelegateBinding extends AbstractBinding
{
    /**
     * The factory callback to be used for object-creation.
     *
     * @var \Closure
     */
    protected $callback;

    /**
     * Create a binding that defers object-creation to a callback function.
     *
     * @param string $typeName Fully-qualified name of the bound type.
     * @param string $scope Scope of the binding.
     * @param integer $options Binding options combined into an integer.
     * @param \Closure $callback The factory callback to be used.
     */
    public function __construct($typeName, $scope, $options, \Closure $callback)
    {
        $this->typeName = (string)$typeName;
        $this->scope = ($scope === NULL) ? NULL : (string)$scope;
        $this->options = (int)$options;
        $this->callback = $callback;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(ContainerInterface $container, InjectionPointInterface $point = NULL)
    {
        return $this->callback->__invoke($point);
    }

    public function __debugInfo()
    {
        $data = get_object_vars($this);

        unset($data['callback']);

        return $data;
    }

    /**
     * Get the callback being used to create the bound object instance.
     *
     * @return Closure
     */
    public function getCallback()
    {
        return $this->callback;
    }

    /**
     * Bind the callback to a new this (needed when cloning a compiled container).
     *
     * @param object $object
     */
    public function bindCallback($object)
    {
        $this->callback = $this->callback->bindTo($object, $object);
    }
}
