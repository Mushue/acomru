<?php

/*
 * This file is part of KoolKode Context and Dependency Injection.
*
* (c) Martin Schröder <m.schroeder2007@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace KoolKode\Context\Bind;

/**
 * Loader for custom DI container initializers
 *
 * @author Martin Schröder
 */
class ContainerInitializerLoader implements \Countable, \IteratorAggregate
{
    /**
     * Holds all registered initializers keyed to their type names.
     *
     * @var array<string, ContainerInitializerInterface>
     */
    protected $initializers = [];

    /**
     * get the number of registered container initialiters.
     *
     * @return integer
     */
    public function count()
    {
        return count($this->initializers);
    }

    /**
     * Get an iterator that can be used to traverse initializers.
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->initializers);
    }

    /**
     * Convert this loader into an array of initializers.
     *
     * @return array<ContainerInitializerInterface>
     */
    public function toArray()
    {
        return array_values($this->initializers);
    }

    /**
     * Get an MD5 hash computed from the sorted type names of all initializers.
     *
     * @return string
     */
    public function getHash()
    {
        $names = array_keys($this->initializers);
        sort($names);

        return md5(implode('|', $names));
    }

    /**
     * Get the time of the most recent modification to an initializer.
     *
     * @return integer
     */
    public function getLastModified()
    {
        $mtime = 0;

        foreach ($this->initializers as $initializer) {
            $mtime = max($mtime, filemtime((new \ReflectionClass(get_class($initializer)))->getFileName()));
        }

        return $mtime;
    }

    /**
     * Register a container initializer if it has not been registered yet.
     *
     * @param ContainerInitializerInterface $initializer
     * @return ContainerInitializerLoader
     */
    public function registerInitializer(ContainerInitializerInterface $initializer)
    {
        $key = get_class($initializer);

        if (empty($this->initializers[$key])) {
            $this->initializers[$key] = $initializer;
        }

        return $this;
    }
}
