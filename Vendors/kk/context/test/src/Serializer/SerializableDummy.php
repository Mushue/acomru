<?php

namespace KoolKode\Context\Serializer;

class SerializableDummy implements \Serializable
{
    public $revived = false;

    public $title;

    public function __construct($title)
    {
        $this->title = (string)$title;
    }

    public function serialize()
    {
        return json_encode($this->title);
    }

    public function unserialize($serialized)
    {
        $this->revived = true;
        $this->title = json_decode($serialized);
    }
}
