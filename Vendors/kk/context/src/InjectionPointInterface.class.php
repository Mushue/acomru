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
 * Exposes information about the method that is the target of an injection.
 *
 * @author Martin Schröder
 */
interface InjectionPointInterface
{
    /**
     * Get the name of the type that receives the injection.
     *
     * @return string
     */
    public function getTypeName();

    /**
     * Get the name of the injection method.
     *
     * @return string
     */
    public function getMethodName();
}
