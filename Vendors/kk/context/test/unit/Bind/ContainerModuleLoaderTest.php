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

use KoolKode\Context\TestContainerInitializer;

/**
 * @covers \KoolKode\Context\Bind\ContainerModuleLoader
 */
class ContainerModuleLoaderTest extends \PHPUnit_Framework_TestCase
{
    public function testCanCreateAndAccessEmptyLoader()
    {
        $loader = new ContainerModuleLoader();

        $this->assertCount(0, $loader);
        $this->assertEquals([], iterator_to_array($loader->getIterator()));
        $this->assertEquals([], $loader->toArray());
        $this->assertEquals(0, $loader->getLastModified());
        $this->assertEquals(md5(''), $loader->getHash());
    }

    public function testCanRegisterInitializer()
    {
        $loader = new ContainerModuleLoader();
        $test = new TestContainerModule();

        $this->assertCount(0, $loader);

        $loader->registerModule($test);
        $this->assertCount(1, $loader);

        $loader->registerModule($test);
        $this->assertCount(1, $loader);

        $ref = new \ReflectionObject($test);
        $this->assertEquals(filemtime($ref->getFileName()), $loader->getLastModified());
        $this->assertEquals(md5($ref->name), $loader->getHash());
    }
}
