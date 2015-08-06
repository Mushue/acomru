<?php

/*
 * This file is part of KoolKode Utilities.
*
* (c) Martin Schröder <m.schroeder2007@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace KoolKode\Util;

/**
 * @covers \KoolKode\Util\NamespaceContext
 */
class NamespaceContextTest extends \PHPUnit_Framework_TestCase
{
    public function testCanCreateDefaultNamespaceContext()
    {
        $context = new NamespaceContext();
        $context->addImport('My\Foo');
        $context->addImport('My\Bar');

        $this->assertEquals('', $context->getNamespace());
        $this->assertTrue($context->isGlobalNamespace());

        $revived = unserialize(serialize($context));
        $this->assertEquals($context, $revived);
        $this->assertNotSame($context, $revived);
    }

    public function testCanLookupNamespace()
    {
        $context = new NamespaceContext('\My\Context');
        $this->assertEquals('My\Context', $context->lookup(''));
    }

    public function testCanUtilizeImports()
    {
        $context = new NamespaceContext('Test');
        $context->addImport('My\Foo');
        $context->addImport('Bar');
        $context->addAliasedImport('My', 'My\InnerNamespace');

        $this->assertTrue($context->hasImport('Foo'));
        $this->assertTrue($context->hasImport('Bar'));
        $this->assertTrue($context->hasImport('My'));

        $this->assertEquals('My\Foo', $context->lookup('Foo'));
        $this->assertEquals('Bar', $context->lookup('Bar'));
        $this->assertEquals('My\InnerNamespace\Foo', $context->lookup('My\Foo'));
        $this->assertEquals('Test\Bazinga', $context->lookup('Bazinga'));
        $this->assertEquals('stdClass', $context->lookup('\stdClass'));
    }

    public function testCanCompileNamespaceContext()
    {
        $context = new NamespaceContext('My\Test');
        $context->addImport('My\Foo\Bar');
        $context->addAliasedImport('Dummy', '\My\Test\Fixture');

        $compiled = eval('return ' . $context->compile() . ';');
        $this->assertEquals($context, $compiled);
        $this->assertNotSame($context, $compiled);
    }
}
