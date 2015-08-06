<?php

/*
 * This file is part of KoolKode Context and Dependency Injection.
*
* (c) Martin Schröder <m.schroeder2007@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace KoolKode\Context\Scope;

class FoxyProxy
{
    public $message = 'Hello world';

    public function doSomething()
    {
        return 'Hello from ' . get_class($this);
    }

    public function __call($method, array $args)
    {
        if ($method === 'countArgs') {
            return count($args);
        }

        throw new \BadMethodCallException(sprintf('Method not found: "%s"', $method));
    }
}
