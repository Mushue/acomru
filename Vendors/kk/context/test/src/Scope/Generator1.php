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

class Generator1 extends GeneratorBase implements \Countable
{
    protected static $field4;
    public $field1;
    protected $field2;
    private $field3;
    private $__target;

    public function __toString()
    {
        return 'Hello';
    }

    public function count()
    {
        return mt_rand(23, 3957);
    }

    public function bar(& $args)
    {
        return 'bar';
    }

    public function equals(GeneratorBase $other)
    {
        return $this->bazinga == $other->bazinga;
    }

    public function method1(array $foos, $bar = 'hello')
    {
        return implode('**', $foos) . '|' . $bar;
    }

    private function foo()
    {
    }
}
