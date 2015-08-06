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

abstract class AbstractSetterFilter implements SetterFilterInterface
{
    protected $regex;

    public function __construct($name)
    {
        $this->regex = "'^";

        foreach (func_get_args() as $i => $arg) {
            if ($i != 0) {
                $this->regex .= '|';
            }

            if (!preg_match("'^set\w'i", $arg)) {
                $arg = 'set' . ucfirst($arg);
            }

            $this->regex .= '(?:' . str_replace('*', '.+', preg_quote($arg, "'")) . ')';
        }

        $this->regex .= "$'i";
    }
}
