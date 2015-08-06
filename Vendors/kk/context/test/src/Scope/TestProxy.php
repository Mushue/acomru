<?php

namespace KoolKode\Context\Scope;

use KoolKode\Context\Bind\DelegateBinding;

class TestProxy
{
    protected $binding;

    protected $scope;

    public function __construct(DelegateBinding $binding, ScopeManagerInterface $scope)
    {
        $this->binding = $binding;
        $this->scope = $scope;
    }

    public function getBinding()
    {
        return $this->binding;
    }

    public function getScope()
    {
        return $this->scope;
    }
}
