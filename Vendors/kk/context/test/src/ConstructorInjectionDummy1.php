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

class ConstructorInjectionDummy1
{
    public $container;
    public $factory;

    public function __construct(ContainerInterface $container, TestFactory $factory)
    {
        $this->container = $container;
        $this->factory = $factory;
    }
}
