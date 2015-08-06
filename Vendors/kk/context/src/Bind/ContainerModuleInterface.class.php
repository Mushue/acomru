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
 * Module that is integrated with the container lifecycle.
 *
 * @author Martin Schröder
 */
interface ContainerModuleInterface
{
    /**
     * Is called when the container configuration is being assembled.
     *
     * @param ContainerBuilder $builder
     */
    public function build(ContainerBuilder $builder);

    /**
     * Is called after the container has been configured and created, any number of additional
     * methods starting with "boot" may be declared, they are allowed to declare dependencies
     * as arguments that will be populated from the DI container.
     */
    public function boot();
}
