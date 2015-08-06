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

/**
 * Container initializers can post-process created objects.
 *
 * @author Martin Schröder
 */
interface ContainerInitializerInterface
{
    /**
     * Post-process an object instance after it has been created.
     *
     * HINT: This method must eighter return a "falsy" value or an object instance that is to be used
     * instead of the passed object (this allows for decorators based on an interface implemented by
     * the type of the created object).
     *
     * @param object $object
     * @param ContainerInterface $container
     * @return mixed
     */
    public function initializeObject($object, ContainerInterface $container);
}
