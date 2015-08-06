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

class CreationTester
{
    public $container;
    public $message;

    public function __construct(ContainerInterface $container, $message)
    {
        $this->container = $container;
        $this->message = trim($message);
    }
}
