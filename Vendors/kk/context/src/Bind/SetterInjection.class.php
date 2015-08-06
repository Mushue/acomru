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

final class SetterInjection extends Marker implements SetterFilterInterface
{
    const CONVENTION = 2;

    public $policy;

    public function __construct($policy = NULL)
    {
        if ($policy !== NULL) {
            if ($policy instanceof SetterFilterInterface) {
                $this->policy = $policy;
            } elseif ($policy == self::CONVENTION) {
                $this->policy = (int)$policy;
            } else {
                throw new \InvalidArgumentException(sprintf('Setter injection policy invalid: %s', gettype($policy)));
            }
        }
    }

    public function isConvention()
    {
        return $this->policy === self::CONVENTION;
    }

    public function isFilter()
    {
        return $this->policy === NULL || $this->policy instanceof SetterFilterInterface;
    }

    public function accept(\ReflectionMethod $method)
    {
        if ($this->policy instanceof SetterFilterInterface) {
            return 'set' === strtolower(substr($method->getName(), 0, 3)) && $this->policy->accept($method);
        }

        switch ($this->policy) {
            case self::CONVENTION:
                return 'inject' === strtolower(substr($method->getName(), 0, 6));
        }

        return 'set' === strtolower(substr($method->getName(), 0, 3));
    }
}
