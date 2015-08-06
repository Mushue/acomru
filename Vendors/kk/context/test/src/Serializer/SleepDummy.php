<?php

namespace KoolKode\Context\Serializer;

class SleepDummy
{
    public $a;
    public $d;
    protected $b;
    private $c;

    public function __construct($a, $b, $c, $d)
    {
        $this->a = (string)$a;
        $this->b = (string)$b;
        $this->c = (string)$c;
        $this->d = (string)$d;
    }

    public function __sleep()
    {
        return ['a', 'b', 'c'];
    }

    public function getB()
    {
        return $this->b;
    }

    public function getC()
    {
        return $this->c;
    }
}
