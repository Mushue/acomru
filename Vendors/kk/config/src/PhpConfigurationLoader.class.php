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
 * Loads config data from PHP files by including them, the files need to return an array
 * of config data.
 *
 * @author Martin Schröder
 */
class PhpConfigurationLoader implements ConfigurationLoaderInterface
{
    /**
     * {@inheritdoc}
     */
    public function isSupported(\SplFileInfo $source)
    {
        return 'php' === strtolower($source->getExtension());
    }

    /**
     * {@inheritdoc}
     */
    public function load(\SplFileInfo $source, array $params = [])
    {
        $file = $source->getPathname();

        if (!is_file($file) || !is_readable($file)) {
            throw new \RuntimeException(sprintf('Unable to load configuration file: "%s"', $file));
        }

        return $this->includeConfigFile($file, $params);
    }

    /**
     * Extracts given params into local scope and includes the config file.
     *
     * @param string $file
     * @param array <string, mixed> $params
     */
    protected function includeConfigFile()
    {
        extract(func_get_arg(1));

        return (array)require func_get_arg(0);
    }
}
