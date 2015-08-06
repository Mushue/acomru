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

class SetterInjectionExample
{
    public $foo;
    public $bar;
    public $baz;

    public static function injectSomethingStatic(\stdClass $something)
    {
        throw new \BadMethodCallException('Static method must not be considered by setter injection');
    }

    public function injectFoo(\stdClass $foo)
    {
        $this->foo = $foo;
    }

    public function leFou()
    {
    }

    public function setFoo(\stdClass $foo)
    {
        $this->foo = $foo;
    }

    public function setBar(\stdClass $bar)
    {
        $this->bar = $bar;
    }

    /**
     * @param \stdClass $foo
     */
    public function passFoo(\stdClass $foo)
    {
        $this->foo = $foo;
    }

    protected function injectBar(\stdClass $bar)
    {
        throw new \BadMethodCallException('Protected method must not be considered by setter injection');
    }

    private function injectBaz(\stdClass $baz)
    {
        throw new \BadMethodCallException('Private method must not be considered by setter injection');
    }
}
