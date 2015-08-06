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

/**
 * Provides methods to be mixed into all scoped proxies.
 *
 * @author Martin Schröder
 */
trait ScopedProxyTrait
{
    public final function __destruct()
    {
    }

    public final function serialize()
    {
        throw new \RuntimeException(sprintf('Scoped proxy of %s must not be serialized', get_parent_class($this)));
    }

    /**
     * @codeCoverageIgnore
     */
    public final function unserialize($serialized)
    {
        throw new \RuntimeException(sprintf('Scoped proxy of %s must not be unserialized', get_parent_class($this)));
    }

    /**
     * @codeCoverageIgnore
     */
    public function __debugInfo()
    {
        if ($this->__K2Target->offsetExists($this)) {
            $target = $this->__K2Target[$this];
        } else {
            $target = NULL;
        }

        return [
            'typeName' => $this->__K2Binding->getTypeName(),
            'scope' => $this->__K2Scope,
            'instance' => $target
        ];
    }

    public final function __isset($prop)
    {
        if (!$this->__K2Target->offsetExists($this)) {
            $this->__K2Scope->activateInstance($this);
        }

        return isset($this->__K2Target[$this]->$prop);
    }

    public final function & __get($prop)
    {
        if (!$this->__K2Target->offsetExists($this)) {
            $this->__K2Scope->activateInstance($this);
        }

        $value = &$this->__K2Target[$this]->$prop;

        return $value;
    }

    public final function __set($prop, $value)
    {
        if (!$this->__K2Target->offsetExists($this)) {
            $this->__K2Scope->activateInstance($this);
        }

        $this->__K2Target[$this]->$prop = $value;
    }

    public final function __call($method, array $args)
    {
        if (!$this->__K2Target->offsetExists($this)) {
            $this->__K2Scope->activateInstance($this);
        }

        switch (count($args)) {
            case 0:
                return $this->__K2Target[$this]->$method();
            case 1:
                return $this->__K2Target[$this]->$method($args[0]);
            case 2:
                return $this->__K2Target[$this]->$method($args[0], $args[1]);
            case 3:
                return $this->__K2Target[$this]->$method($args[0], $args[1], $args[2]);
            case 4:
                return $this->__K2Target[$this]->$method($args[0], $args[1], $args[2], $args[3]);
            case 5:
                return $this->__K2Target[$this]->$method($args[0], $args[1], $args[2], $args[3], $args[4]);
        }

        return call_user_func_array([$this->__K2Target[$this], $method], $args);
    }

    public final function K2UnwrapScopedProxy()
    {
        if (!$this->__K2Target->offsetExists($this)) {
            $this->__K2Scope->activateInstance($this);
        }

        return $this->__K2Target[$this];
    }

    public final function K2GetProxyBinding()
    {
        return $this->__K2Binding;
    }

    public final function K2IsProxyBound()
    {
        return $this->__K2Target->offsetExists($this);
    }
}
