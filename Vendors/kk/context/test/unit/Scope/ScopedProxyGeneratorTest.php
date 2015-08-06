<?php

/*
 * This file is part of KoolKode Context and Dependency Injection.
*
* (c) Martin Schröder <m.schroeder2007@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace KoolKode\Context\Scope;

/**
 * @covers \KoolKode\Context\Scope\ScopedProxyGenerator
 * @covers \KoolKode\Context\Scope\ScopedProxyTrait
 */
class ScopedProxyGeneratorTest extends \PHPUnit_Framework_TestCase
{
    protected $generator;

    public function testCanCreateScopedProxyFromConcreteClass()
    {
        $source = new \ReflectionClass('KoolKode\Context\Scope\Generator1');
        $code = $this->generator->generateProxyCode($source);

        eval($code);

        $ref = new \ReflectionClass($source->name . '__scoped');

        $this->assertFalse($ref->isAbstract());
        $this->assertTrue($ref->isFinal());
        $this->assertFalse($ref->isInterface());
        $this->assertTrue($ref->isInstantiable());
        $this->assertTrue($ref->implementsInterface('KoolKode\Context\Scope\ScopedProxyInterface'));
        $this->assertEquals($source->name, $ref->getParentClass()->name);

        $method = $ref->getMethod('__toString');
        $this->assertTrue($method->isPublic());
        $this->assertEquals($source->name, $method->getDeclaringClass()->name);

        $method = $ref->getMethod('count');
        $this->assertTrue($method->isPublic());

        $method = $ref->getMethod('invokeMe');
        $this->assertTrue($method->isPublic());
        $this->assertTrue($method->getParameters()[0]->isCallable());

        $method = $ref->getMethod('equals');
        $this->assertTrue($method->isPublic());
        $this->assertFalse($method->isAbstract());
        $this->assertEquals('KoolKode\Context\Scope\GeneratorBase', $method->getParameters()[0]->getClass()->name);

        $method = $ref->getMethod('method1');
        $this->assertTrue($method->isPublic());
        $this->assertTrue($method->getParameters()[0]->isArray());
        $this->assertEquals('hello', $method->getParameters()[1]->getDefaultValue());

        $method = $ref->getMethod('bar');
        $this->assertTrue($method->isPublic());
        $this->assertTrue($method->getParameters()[0]->isPassedByReference());

        $method = $ref->getMethod('getRef');
        $this->assertTrue($method->isProtected());
        $this->assertTrue($method->returnsReference());

        $this->assertTrue($ref->hasMethod('foo'));
        $this->assertTrue($ref->getMethod('foo')->isPrivate());
        $this->assertEquals($source->name, $ref->getMethod('foo')->getDeclaringClass()->name);
    }

    public function testCanCreateScopedProxyFromAbstractClass()
    {
        $source = new \ReflectionClass('KoolKode\Context\Scope\GeneratorBase');
        $code = $this->generator->generateProxyCode($source);

        eval($code);

        $ref = new \ReflectionClass($source->name . '__scoped');

        $this->assertFalse($ref->isAbstract());
        $this->assertTrue($ref->isFinal());
        $this->assertFalse($ref->isInterface());
        $this->assertTrue($ref->isInstantiable());
        $this->assertTrue($ref->implementsInterface('KoolKode\Context\Scope\ScopedProxyInterface'));
        $this->assertEquals($source->name, $ref->getParentClass()->name);

        $method = $ref->getMethod('count');
        $this->assertTrue($method->isPublic());

        $method = $ref->getMethod('invokeMe');
        $this->assertTrue($method->isPublic());
        $this->assertTrue($method->getParameters()[0]->isCallable());

        $method = $ref->getMethod('equals');
        $this->assertTrue($method->isPublic());
        $this->assertFalse($method->isAbstract());
        $this->assertEquals($source->name, $method->getParameters()[0]->getClass()->name);

        $method = $ref->getMethod('bar');
        $this->assertTrue($method->isProtected());
        $this->assertTrue($method->getParameters()[0]->isPassedByReference());

        $method = $ref->getMethod('getRef');
        $this->assertTrue($method->isProtected());
        $this->assertTrue($method->returnsReference());

        $this->assertTrue($ref->hasMethod('foo'));
        $this->assertTrue($ref->getMethod('foo')->isPrivate());
        $this->assertEquals($source->name, $ref->getMethod('foo')->getDeclaringClass()->name);
    }

    public function testCanCreateScopedProxyFromInterface()
    {
        $source = new \ReflectionClass('KoolKode\Context\Scope\GeneratorInterface');
        $code = $this->generator->generateProxyCode($source);

        eval($code);

        $ref = new \ReflectionClass($source->name . '__scoped');

        $this->assertFalse($ref->isAbstract());
        $this->assertTrue($ref->isFinal());
        $this->assertFalse($ref->isInterface());
        $this->assertTrue($ref->isInstantiable());
        $this->assertTrue($ref->implementsInterface('KoolKode\Context\Scope\ScopedProxyInterface'));
        $this->assertTrue($ref->implementsInterface($source->name));

        $method = $ref->getMethod('hello');
        $this->assertTrue($method->isPublic());
        $this->assertTrue($method->getParameters()[0]->isCallable());
        $this->assertTrue($method->getParameters()[0]->isOptional());
        $this->assertNull($method->getParameters()[0]->getDefaultValue());
    }

    /**
     * @expectedException \KoolKode\Context\Scope\ScopedProxyException
     */
    public function testGenerationFailsOnPublicFinalMethod()
    {
        $this->generator->generateProxyCode(new \ReflectionClass('KoolKode\Context\Scope\GeneratorFail'));
    }

    protected function setUp()
    {
        parent::setUp();

        $this->generator = new ScopedProxyGenerator();
    }
}
