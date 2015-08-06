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

abstract class GeneratorBase implements \Countable
{
    protected $bazinga = 'Bazinga!';

    public function invokeMe(callable $func)
    {
        return $func($this);
    }

    public abstract function equals(GeneratorBase $other);

    protected function bar(& $args)
    {
        return 'bar-base';
    }

    protected function & getRef()
    {
        return $this->bazinga;
    }

    private function foo()
    {
    }
}
