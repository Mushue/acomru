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
 * @author Martin Schröder
 */
class ScopeLoader implements \Countable, \IteratorAggregate
{
    protected $scopes = [];

    public function count()
    {
        return count($this->scopes);
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->scopes);
    }

    public function toArray()
    {
        return $this->scopes;
    }

    public function getHash()
    {
        $mtime = 0;
        $scopes = [];

        foreach ($this->scopes as $scope) {
            $scopes[] = get_class($scope);

            $mtime = max($mtime, filemtime((new \ReflectionClass(get_class($scope)))->getFileName()));
        }

        sort($scopes);

        $hash = hash_init('md5');

        foreach ($scopes as $type) {
            hash_update($hash, '|' . $type);
        }

        hash_update($hash, '||' . $mtime);

        return hash_final($hash);
    }

    public function registerScope(ScopeManagerInterface $scope)
    {
        if (isset($this->scopes[$scope->getScope()])) {
            throw new DuplicateScopeException(sprintf('Scope "%s" is already registered', $scope->getScope()));
        }

        $this->scopes[$scope->getScope()] = $scope;
    }
}
