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

/**
 * Is used by the container compiler to inline instructions during code generation.
 *
 * @codeCoverageIgnore
 *
 * @author Martin Schröder
 */
class CompiledCodeFragment
{
    protected $source;

    public function __construct($source)
    {
        $this->source = (string)$source;
    }

    public function __toString()
    {
        return $this->source;
    }
}
