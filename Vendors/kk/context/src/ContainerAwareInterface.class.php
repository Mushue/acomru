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
 * Provides interface injection for the DI container, it is not a good practice to inject
 * the container due to hidden dependencies, look for alternative solutions
 * before you consider using this.
 *
 * @author Martin Schröder
 */
interface ContainerAwareInterface
{
    /**
     * Injects the DI container instance into the target object.
     *
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container);
}
