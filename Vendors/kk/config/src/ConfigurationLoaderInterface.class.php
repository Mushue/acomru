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
 * Contrat for a pluggable configuration loader implementation.
 *
 * @author Martin Schröder
 */
interface ConfigurationLoaderInterface
{
    /**
     * Check if the loader can load the given file.
     *
     * @param \SplFileInfo $source
     * @return boolean
     */
    public function isSupported(\SplFileInfo $source);

    /**
     * Load config data from the given file and convert it into an array.
     *
     * @param \SplFileInfo $source
     * @param array <string, mixed> $params
     * @return array
     */
    public function load(\SplFileInfo $source, array $params = []);
}
