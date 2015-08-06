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
 * Dummy implementation of a container module, does nothing except for empty
 * methods being called.
 *
 * @author Martin Schröder
 */
abstract class AbstractContainerModule implements ContainerModuleInterface
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $builder)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function boot()
    {
    }
}
