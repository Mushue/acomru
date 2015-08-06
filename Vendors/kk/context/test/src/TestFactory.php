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

use KoolKode\Config\Configuration;

class TestFactory
{
    public function create(Configuration $config)
    {
        $obj = new \stdClass();
        $obj->origin = get_class($this);

        foreach ($config as $k => $v) {
            $obj->$k = $v;
        }

        return $obj;
    }
}
