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

final class TestMarker extends Marker
{
    public $name;

    public function __construct($name = '')
    {
        $this->name = trim($name);
    }
}
