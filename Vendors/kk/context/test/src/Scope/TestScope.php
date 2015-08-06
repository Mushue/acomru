<?php

namespace KoolKode\Context\Scope;

class TestScope extends AbstractScopeManager
{
    const SCOPE = '**foo**';

    public function getScope()
    {
        return self::SCOPE;
    }

    public function enter($object = NULL)
    {
        return parent::bindContext($object);
    }

    public function leave($object, $terminate = true)
    {
        return parent::unbindContext($object, $terminate);
    }

    public function initializeProxy($typeName, callable $factory = NULL)
    {
        return parent::initializeProxy($typeName, $factory);
    }
}
