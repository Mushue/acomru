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

use KoolKode\Context\Bind\Binding;
use KoolKode\Context\ContainerAwareMock;

class ScopedProxyMock extends ContainerAwareMock implements ScopedProxyInterface
{
    protected $bindingName;

    protected $instance;

    public function __construct($bindingName = '', $instance = NULL)
    {
        $this->bindingName = (string)$bindingName;
        $this->instance = $instance;
    }

    public function serialize()
    {

    }

    public function unserialize($serialized)
    {

    }

    public function K2ClearInstance()
    {

    }

    public function K2GetProxyBinding()
    {
        return new Binding($this->bindingName);
    }

    public function K2IsProxyBound()
    {

    }

    public function K2UnwrapScopedProxy()
    {
        return $this->instance;
    }
}
