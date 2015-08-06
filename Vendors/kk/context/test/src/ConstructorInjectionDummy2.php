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

class ConstructorInjectionDummy2
{
    public $containers = [];

    public function __construct(ContainerInterface $c1, ContainerInterface $c2, ContainerInterface $c3,
                                ContainerInterface $c4, ContainerInterface $c5, ContainerInterface $c6, ContainerInterface $c7)
    {
        $this->containers[] = $c1;
        $this->containers[] = $c2;
        $this->containers[] = $c3;
        $this->containers[] = $c4;
        $this->containers[] = $c5;
        $this->containers[] = $c6;
        $this->containers[] = $c7;
    }
}
