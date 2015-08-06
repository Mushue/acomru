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

class ConstructorArgs3
{
    public $a1, $a2, $a3;

    public function __construct(\stdClass $a1, \stdClass $a2, \stdClass $a3)
    {
        $this->a1 = $a1;
        $this->a2 = $a2;
        $this->a3 = $a3;
    }
}
