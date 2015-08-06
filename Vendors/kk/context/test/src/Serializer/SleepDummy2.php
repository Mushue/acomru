<?php

namespace KoolKode\Context\Serializer;

class SleepDummy2
{
    public function __sleep()
    {
        return ['foo'];
    }
}
