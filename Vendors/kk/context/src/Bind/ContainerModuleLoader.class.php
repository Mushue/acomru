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
 * Loader for custom container modules that supply additional bindings.
 *
 * @author Martin Schröder
 */
class ContainerModuleLoader implements \Countable, \IteratorAggregate
{
    /**
     * All registered container modules by type name.
     *
     * @var array<string, ContainerModuleInterface>
     */
    protected $modules = [];

    /**
     * Get the number of registered container modules.
     *
     * @return integer
     */
    public function count()
    {
        return count($this->modules);
    }

    /**
     * Get an iterator that can be used to traverse all registered modules.
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->modules);
    }

    /**
     * Get an array containing all registered container modules.
     *
     * @return array<ContainerModuleInterface>
     */
    public function toArray()
    {
        return array_values($this->modules);
    }

    /**
     * Get an MD5 hash computed from the sorted type names of all modules.
     *
     * @return string
     */
    public function getHash()
    {
        $names = array_keys($this->modules);
        sort($names);

        return md5(implode('|', $names));
    }

    /**
     * Get the time of the most recent modification to any registered module.
     *
     * @return integer
     */
    public function getLastModified()
    {
        $mtime = 0;

        foreach ($this->modules as $module) {
            $mtime = max($mtime, filemtime((new \ReflectionClass(get_class($module)))->getFileName()));
        }

        return $mtime;
    }

    public function registerModule(ContainerModuleInterface $module)
    {
        $key = get_class($module);

        if (empty($this->modules[$key])) {
            $this->modules[$key] = $module;
        }

        return $this;
    }
}
