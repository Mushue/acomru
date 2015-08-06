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

class SetterInjectionExample2
{
    public $s1, $s2, $s3, $s4, $s5;

    public function set1(\stdClass $s1)
    {
        $this->s1 = $s1;
    }

    public function set2(\stdClass $s1, \stdClass $s2)
    {
        $this->s1 = $s1;
        $this->s2 = $s2;
    }

    public function set3(\stdClass $s1, \stdClass $s2, \stdClass $s3)
    {
        $this->s1 = $s1;
        $this->s2 = $s2;
        $this->s3 = $s3;
    }

    public function set4(\stdClass $s1, \stdClass $s2, \stdClass $s3, \stdClass $s4)
    {
        $this->s1 = $s1;
        $this->s2 = $s2;
        $this->s3 = $s3;
        $this->s4 = $s4;
    }

    public function set5(\stdClass $s1, \stdClass $s2, \stdClass $s3, \stdClass $s4, \stdClass $s5)
    {
        $this->s1 = $s1;
        $this->s2 = $s2;
        $this->s3 = $s3;
        $this->s4 = $s4;
        $this->s5 = $s5;
    }
}
