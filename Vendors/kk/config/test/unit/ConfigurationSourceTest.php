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
 * @covers KoolKode\Config\ConfigurationLoader
 * @covers KoolKode\Config\ConfigurationSource
 */
class ConfigurationSourceTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructionOfConfigurationSource()
    {
        $file = new \SplFileInfo(__FILE__);
        $source = new ConfigurationSource($file, 1337);
        $info = $source->getSource();

        // Assert the passed file info is not re-used (prevents trouble with SPL iterators).
        $this->assertNotSame($file, $source);

        $this->assertEquals($file->getPathname(), $info->getPathname());
        $this->assertEquals(1337, $source->getPriority());
        $this->assertEquals($file->getMTime(), $source->getLastModified());

        $this->assertEquals(md5(str_replace('\\', '/', $file->getPathname())), $source->getKey());
    }

    /**
     * @covers KoolKode\Config\PhpConfigurationLoader
     */
    public function testCanLoadPhpConfigSource()
    {
        $loader = new ConfigurationLoader();
        $loader->registerLoader(new PhpConfigurationLoader());

        $file = new \SplFileInfo(__DIR__ . '/../files/config.php');
        $source = new ConfigurationSource($file);
        $config = $source->loadConfiguration($loader, ['subject' => '*USER*']);

        $data = [
            'message' => 'Hello *USER*',
            'ttl' => 120
        ];
        $this->assertEquals($data, $config->toArray());
    }

    /**
     * @covers KoolKode\Config\YamlConfigurationLoader
     */
    public function testCanLoadYamlConfigSource()
    {
        $loader = new ConfigurationLoader();
        $loader->registerLoader(new YamlConfigurationLoader());

        $file = new \SplFileInfo(__DIR__ . '/../files/config.yml');
        $source = new ConfigurationSource($file);
        $config = $source->loadConfiguration($loader);

        $data = [
            'foo' => [
                'bar' => [
                    'message' => 'Hello World',
                    'public' => true
                ]
            ]
        ];

        $this->assertTrue($config instanceof Configuration);
        $this->assertEquals($data, $config->toArray());
    }

    /**
     * @expectedException \KoolKode\Config\ConfigurationLoadingException
     */
    public function testThrowsExceptionWhenNoConfigFileCannotBeAccessed()
    {
        (new ConfigurationSource(new \SplFileInfo(__DIR__ . '/../files/foooooba.yml')))->loadConfiguration(new ConfigurationLoader());
    }

    /**
     * @expectedException \KoolKode\Config\ConfigurationLoadingException
     */
    public function testThrowsExceptionWhenNoLoaderIsFoundByFileExtension()
    {
        (new ConfigurationSource(new \SplFileInfo(__DIR__ . '/../files/test.fail')))->loadConfiguration(new ConfigurationLoader());
    }

    /**
     * @covers KoolKode\Config\YamlConfigurationLoader
     */
    public function testWillPrependBasePathAndInjectParamsInYamlConfig()
    {
        $loader = new ConfigurationLoader();
        $loader->registerLoader(new YamlConfigurationLoader());

        $source = new ConfigurationSource(new \SplFileInfo(__DIR__ . '/../files/KoolKode.Config.yml'));
        $config = $source->loadConfiguration($loader, ['subject' => 'FOO_BAR']);

        $data = [
            'kk' => [
                'config' => [
                    'test' => [
                        'message' => 'Hello FOO_BAR from config'
                    ]
                ]
            ]
        ];
        $this->assertEquals($data, $config->toArray());
    }
}
