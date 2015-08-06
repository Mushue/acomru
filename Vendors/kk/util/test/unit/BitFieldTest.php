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
 * @covers \KoolKode\Util\BitField
 */
class BitFieldTest extends \PHPUnit_Framework_TestCase
{
    public function testBitFieldConstructor()
    {
        $field1 = new BitField(7);
        $field2 = new BitField('0111');
        $field3 = new BitField($field1);

        $this->assertEquals('111', (string)$field1);
        $this->assertEquals('111', (string)$field2);
        $this->assertEquals('111', (string)$field3);

        $this->assertEquals(7, $field1->toInteger());
        $this->assertEquals(7, $field2->toInteger());
        $this->assertEquals(7, $field3->toInteger());
    }

    public function testDetectsSetBits()
    {
        $field = new BitField('1010');

        $this->assertFalse($field->has(1));
        $this->assertTrue($field->has(2));
        $this->assertFalse($field->has(4));
        $this->assertTrue($field->has(8));
        $this->assertFalse($field->has(16));

        $this->assertTrue($field->has(2 | 8));
        $this->assertFalse($field->has(2 | 4 | 8));

        $this->assertTrue($field->hasAny(2 | 8));
        $this->assertTrue($field->hasAny(2 | 4 | 8));
    }

    public function testCanCheckFlags()
    {
        $field = new BitField(0b1101);
        $data = $field->__debugInfo();
        $flags = $data['flags'];

        $this->assertCount(31, $flags);
        $this->assertTrue($flags[1]);
        $this->assertFalse($flags[2]);
        $this->assertTrue($flags[4]);
        $this->assertTrue($flags[8]);
        $this->assertFalse($flags[16]);
    }

    public function testCanSetAndUnsetBits()
    {
        $field = new BitField(0);

        $this->assertEquals(0, $field->toInteger());
        $field->set(5);
        $this->assertEquals(5, $field->toInteger());
        $field->set(1, false);
        $this->assertEquals(4, $field->toInteger());
    }

    public function testCanClearBitField()
    {
        $field = new BitField(0b101);
        $this->assertEquals(0b101, $field->toInteger());

        $field->clear();
        $this->assertEquals(0, $field->toInteger());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testHasThrowsExceptionOnInvalidArg()
    {
        (new BitField(1))->has('foo');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testHasAnyThrowsExceptionOnInvalidArg()
    {
        (new BitField(1))->hasAny('foo');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSetThrowsExceptionOnInvalidArg()
    {
        (new BitField(1))->set('foo', true);
    }
}
