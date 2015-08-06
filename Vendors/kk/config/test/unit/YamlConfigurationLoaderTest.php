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
 * @covers KoolKode\Config\YamlConfigurationLoader
 */
class YamlConfigurationLoaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \RuntimeException
     */
    public function testWillThrowExceptionIfConfigFileCannotBeAccessed()
    {
        $loader = new YamlConfigurationLoader();
        $loader->load(new \SplFileInfo(__DIR__ . '/foo-bar'));
    }

    public function testWillUseEmptyArrayForEmptyFile()
    {
        $loader = new YamlConfigurationLoader();
        $cfg = $loader->load(new \SplFileInfo(__DIR__ . '/../files/empty.yml'));

        $this->assertEquals([], $cfg);
    }
}
