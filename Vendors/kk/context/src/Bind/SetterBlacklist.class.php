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

class SetterBlacklist extends AbstractSetterFilter
{
    public function accept(\ReflectionMethod $method)
    {
        return preg_match($this->regex, $method->getName()) ? false : true;
    }
}
