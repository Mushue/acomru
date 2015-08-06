<?php

namespace KoolKode\Context\Bind;

/**
 * @covers \KoolKode\Context\Bind\AbstractSetterFilter
 * @covers \KoolKode\Context\Bind\SetterBlacklist
 * @covers \KoolKode\Context\Bind\SetterInjection
 * @covers \KoolKode\Context\Bind\SetterWhitelist
 */
class SetterInjectionTest extends \PHPUnit_Framework_TestCase
{
    public function testWillAcceptAnySetterMethodByDefault()
    {
        $setter = new SetterInjection();

        $this->assertFalse($setter->isConvention());
        $this->assertTrue($setter->isFilter());

        $mock = $this->getMockBuilder(\ReflectionMethod::class)->disableOriginalConstructor()->getMock();
        $mock->expects($this->any())->method('getName')->will($this->returnValue('setFoo'));
        $this->assertTrue($setter->accept($mock));

        $mock = $this->getMockBuilder(\ReflectionMethod::class)->disableOriginalConstructor()->getMock();
        $mock->expects($this->any())->method('getName')->will($this->returnValue('injectFoo'));
        $this->assertFalse($setter->accept($mock));
    }

    public function testWillMatchByConvention()
    {
        $setter = new SetterInjection(SetterInjection::CONVENTION);

        $this->assertTrue($setter->isConvention());
        $this->assertFalse($setter->isFilter());

        $mock = $this->getMockBuilder(\ReflectionMethod::class)->disableOriginalConstructor()->getMock();
        $mock->expects($this->once())->method('getName')->will($this->returnValue('injectFoo'));
        $this->assertTrue($setter->accept($mock));

        $mock = $this->getMockBuilder(\ReflectionMethod::class)->disableOriginalConstructor()->getMock();
        $mock->expects($this->once())->method('getName')->will($this->returnValue('setFoo'));
        $this->assertFalse($setter->accept($mock));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testConstructorThrowsExceptionOnInvalidArgument()
    {
        new SetterInjection('hello');
    }

    public function testCanUtilizeBlacklistFilter()
    {
        $setter = new SetterInjection(new SetterBlacklist('Foo', 'setBar'));
        $this->assertTrue($setter->isFilter());

        $mock = $this->getMockBuilder(\ReflectionMethod::class)->disableOriginalConstructor()->getMock();
        $mock->expects($this->any())->method('getName')->will($this->returnValue('setMessage'));
        $this->assertTrue($setter->accept($mock));

        $mock = $this->getMockBuilder(\ReflectionMethod::class)->disableOriginalConstructor()->getMock();
        $mock->expects($this->any())->method('getName')->will($this->returnValue('setFoo'));
        $this->assertFalse($setter->accept($mock));

        $mock = $this->getMockBuilder(\ReflectionMethod::class)->disableOriginalConstructor()->getMock();
        $mock->expects($this->any())->method('getName')->will($this->returnValue('setBar'));
        $this->assertFalse($setter->accept($mock));
    }

    public function testCanUtilizeWhitelistFilter()
    {
        $setter = new SetterInjection(new SetterWhitelist('Foo', 'setBar'));
        $this->assertTrue($setter->isFilter());

        $mock = $this->getMockBuilder(\ReflectionMethod::class)->disableOriginalConstructor()->getMock();
        $mock->expects($this->any())->method('getName')->will($this->returnValue('setMessage'));
        $this->assertFalse($setter->accept($mock));

        $mock = $this->getMockBuilder(\ReflectionMethod::class)->disableOriginalConstructor()->getMock();
        $mock->expects($this->any())->method('getName')->will($this->returnValue('setFoo'));
        $this->assertTrue($setter->accept($mock));

        $mock = $this->getMockBuilder(\ReflectionMethod::class)->disableOriginalConstructor()->getMock();
        $mock->expects($this->any())->method('getName')->will($this->returnValue('setBar'));
        $this->assertTrue($setter->accept($mock));
    }
}
