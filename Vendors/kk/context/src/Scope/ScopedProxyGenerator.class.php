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

use KoolKode\Util\ReflectionTrait;

/**
 * Creates scoped proxies based on data assembled using reflection.
 *
 * @author Martin Schröder
 */
class ScopedProxyGenerator
{
    use ReflectionTrait;

    /**
     * Generates the PHP code of a scoped proxy for the given type.
     *
     * @param \ReflectionClass $type
     * @return string
     */
    public function generateProxyCode(\ReflectionClass $ref)
    {
        $methods = $this->collectProxyMethods($ref);
        $implements = ['\KoolKode\Context\Scope\ScopedProxyInterface'];

        $code = 'namespace ' . $ref->getNamespaceName() . ' { ';
        $code .= 'if(!class_exists(' . var_export($ref->name . '__scoped', true) . ', false)) { ';
        $code .= 'final class ' . $ref->getShortName() . '__scoped';

        if ($ref->isInterface()) {
            $implements[] = '\\' . $ref->name;
        } else {
            $code .= ' extends \\' . $ref->name;
        }

        $code .= ' implements ' . implode(', ', $implements) . ' { ';
        $code .= 'use \KoolKode\Context\Scope\ScopedProxyTrait; ';

        $code .= 'private $__K2Binding, $__K2Scope, $__K2Target; ';

        $code .= 'public final function __construct(\KoolKode\Context\Bind\BindingInterface $binding, \KoolKode\Context\Scope\ScopeManagerInterface $scope, \SplObjectStorage & $target) { ';
        $code .= '$this->__K2Binding = $binding; $this->__K2Scope = $scope; $this->__K2Target = & $target; ';

        $fieldNames = array_unique($this->collectRemovedFieldNames($ref));

        if (!empty($fieldNames)) {
            $code .= ' unset(';

            foreach ($fieldNames as $i => $name) {
                if ($i != 0) {
                    $code .= ', ';
                }

                $code .= '$this->' . $name;
            }

            $code .= ');';
        }

        $code .= ' } ';

        foreach ($methods as $method) {
            $code .= $this->buildMethodSignature($method, true);

            $code .= ' { ';
            $code .= 'if(!$this->__K2Target->offsetExists($this)) { $this->__K2Scope->activateInstance($this); } ';
            $code .= 'return call_user_func_array([$this->__K2Target[$this], __FUNCTION__], func_get_args()); ';
            $code .= '} ';
        }

        return $code . ' } } } ';
    }

    /**
     * Collects all public instance methods of the given type using reflection.
     *
     * @param \ReflectionClass $ref
     * @return array<string, \ReflectionMethod>
     *
     * @throws ScopedProxyException
     */
    protected function collectProxyMethods(\ReflectionClass $ref)
    {
        $methods = [];

        foreach ($ref->getMethods() as $method) {
            if ($method->isStatic() || $method->isPrivate()) {
                continue;
            }

            if (substr($method->getName(), 0, 2) == '__') {
                continue;
            }

            if ($method->isFinal()) {
                throw new ScopedProxyException(sprintf('Unable to build scoped proxy due to final method %s->%s()', $ref->getName(), $method->getName()));
            }

            $methods[strtolower($method->getName())] = $method;
        }

        return $methods;
    }

    protected function collectRemovedFieldNames(\ReflectionClass $ref)
    {
        $fields = [];

        foreach ($ref->getProperties() as $prop) {
            if ($prop->isStatic() || $prop->isPrivate()) {
                continue;
            }

            $fields[] = $prop->getName();
        }

        return $fields;
    }
}
