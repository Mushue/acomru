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

class ContainerAwareMock implements ContainerAwareInterface
{
    public $container;

    public $message;

    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function setMessage($message)
    {
        $this->message = (string)$message;
    }
}
