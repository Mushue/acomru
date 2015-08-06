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

/**
 * Contract for all bindings.
 *
 * @author Martin Schröder
 */
interface BindingInterface
{
    /**
     * Bit 6: Implementation bindings tie the object to a specific implementation class that is
     * auto-created by the DI container passing any number of constructor args resolved by the
     * container (performing constructor injection in addition to default injection methods).
     *
     * @var integer
     */
    const TYPE_IMPLEMENTATION = 0b1;

    /**
     * Bit 7: Alias bindings delegate to another binding and return the created object, custom scoping
     * of alias bindings is not supported, the scope is equivalent to the target binding's scope.
     *
     * @var integer
     */
    const TYPE_ALIAS = 0b10;

    /**
     * Bit 8: Factory bindings create their target object using a closure that may accept
     * any number of parameters that are being resolved by the DI container and return the
     * object instance.
     *
     * @var integer
     */
    const TYPE_FACTORY = 0b100;

    /**
     * Bit 9: Factory alias bindings refer to another bound type implementing a method that
     * will be used in order to create the bound instance.
     *
     * @var integer
     */
    const TYPE_FACTORY_ALIAS = 0b1000;

    /**
     * Bits 6 to 9 will be checked to find the type of a binding.
     *
     * @var integer
     */
    const MASK_TYPE = 0b1111;

    /**
     * Generates a unique name for this binding.
     *
     * @return string
     */
    public function __toString();

    /**
     * Get the fully-qualified name of the bound type.
     *
     * @return string
     */
    public function getTypeName();

    /**
     * Get the scope of the binding.
     *
     * @return string or NULL when scope is Dependent.
     */
    public function getScope();

    /**
     * Get the binding options (that is an integer containing the combined flags being set).
     *
     * @return integer
     */
    public function getOptions();
}
