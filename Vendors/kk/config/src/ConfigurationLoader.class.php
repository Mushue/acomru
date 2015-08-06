<?php

/*
 * This file is part of KoolKode Config.
*
* (c) Martin Schröder <m.schroeder2007@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace KoolKode\Config;

/**
 * Combines configuration loaders and allows for picking the right loader based
 * on a given file.
 *
 * @author Martin Schröder
 */
class ConfigurationLoader
{
    /**
     * Registered config loaders.
     *
     * @var \SplObjectStorage
     */
    protected $loaders;

    public function __construct()
    {
        $this->loaders = new \SplObjectStorage();
    }

    /**
     * Register a new config loader.
     *
     * @param ConfigurationLoaderInterface $loader
     */
    public function registerLoader(ConfigurationLoaderInterface $loader)
    {
        $this->loaders->attach($loader);
    }

    /**
     * Picks a config loader for the given file and returns it.
     *
     * @param \SplFileInfo $source
     * @return ConfigurationLoaderInterface
     *
     * @throws \OutOfBoundsException When no loader is able to load the given file.
     */
    public function findLoader(\SplFileInfo $source)
    {
        foreach ($this->loaders as $loader) {
            if ($loader->isSupported($source)) {
                return $loader;
            }
        }

        throw new \OutOfBoundsException(sprintf('No configuration loader found for "%s"', $source->getPathname()));
    }
}
