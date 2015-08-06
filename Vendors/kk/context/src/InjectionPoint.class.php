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

/**
 * Simple injection point implementation.
 *
 * @author Martin Schröder
 */
class InjectionPoint implements InjectionPointInterface
{
    protected $typeName;

    protected $methodName;

    /**
     * Crate an injection point for the given type and method.
     *
     * @param string $typeName
     * @param string $methodName
     */
    public function __construct($typeName, $methodName)
    {
        $this->typeName = (string)$typeName;
        $this->methodName = (string)$methodName;
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
    public function getMethodName()
    {
        return $this->methodName;
    }
}
