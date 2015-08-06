<?php

/*
 * This file is part of KoolKode Utilities.
*
* (c) Martin Schröder <m.schroeder2007@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace KoolKode\Util;

class TestReflection
{
    use ReflectionTrait;

    public function getType(\ReflectionParameter $param)
    {
        return $this->getParamType($param);
    }

    public function exportField(\ReflectionProperty $prop)
    {
        return $this->buildFieldSignature($prop);
    }

    public function exportMethod(\ReflectionMethod $method, $skipAbstract = false, $skipDefaultValues = false)
    {
        return $this->buildMethodSignature($method, $skipAbstract, $skipDefaultValues);
    }

    public function exportLiteral($literal)
    {
        return $this->buildLiteralCode($literal);
    }

    public function test1(\stdClass $foo)
    {
    }

    public function test2($foo)
    {
    }

    public function test3(array $foo)
    {
    }

    public function test4(callable $foo)
    {
    }

    public function test5(self $foo)
    {
    }
}
