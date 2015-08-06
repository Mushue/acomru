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

use Symfony\Component\Yaml\Yaml;

/**
 * Parses YAML files and returns parsed data as an array.
 *
 * @author Martin Schröder
 */
class YamlConfigurationLoader implements ConfigurationLoaderInterface
{
    /**
     * {@inheritdoc}
     */
    public function isSupported(\SplFileInfo $source)
    {
        return in_array(strtolower($source->getExtension()), ['yml', 'yaml']);
    }

    /**
     * {@inheritdoc}
     */
    public function load(\SplFileInfo $source, array $params = [])
    {
        return (array)Yaml::parse($this->loadSource($source, $params));
    }

    /**
     * Loads the contents of the given file and replaces %-delimited placeholders
     * with the given values.
     *
     * @param \SplFileInfo $source
     * @param array <string, mixed> $params
     * @return string
     *
     * @throws \RuntimeException When the file could not be accessed.
     */
    protected function loadSource(\SplFileInfo $source, array $params = [])
    {
        $cfg = @file_get_contents($source->getPathname());

        if ($cfg === false) {
            throw new \RuntimeException(sprintf('Configuration source not found: "%s"', $source->getPathname()));
        }

        $replacements = [];

        foreach ($params as $k => $v) {
            $replacements['%' . $k . '%'] = trim($v);
        }

        return trim(strtr($cfg, $replacements));
    }
}
