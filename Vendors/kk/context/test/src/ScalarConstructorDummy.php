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

class ScalarConstructorDummy
{
    const DUMMY_OPT1 = 'opt1';

    public $value;

    public function __construct($value)
    {
        $this->value = $value;
    }
}
