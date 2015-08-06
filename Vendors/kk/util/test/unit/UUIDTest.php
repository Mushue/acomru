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
 * @covers \KoolKode\Util\UUID
 */
class UUIDTest extends \PHPUnit_Framework_TestCase
{
    public function testCanConvertToString()
    {
        $str = '12345678-1234-1234-1234-123456789012';
        $uuid1 = new UUID($str);
        $uuid2 = new UUID(str_replace('-', '', $str));
        $uuid3 = new UUID(hex2bin(str_replace('-', '', $str)));

        $this->assertEquals($str, (string)$uuid1);
        $this->assertEquals($str, (string)$uuid2);
        $this->assertEquals($str, (string)$uuid3);
    }

    public function testCanCreateCompactString()
    {
        $str = '12345678-1234-1234-1234-123456789012';
        $uuid = new UUID($str);

        $this->assertEquals($str, $uuid->toString());
        $this->assertEquals(str_replace('-', '', $str), $uuid->toString(true));
    }

    public function testCanCreateBinaryString()
    {
        $str = 'abcdef01-2345-6789-0123-0123456789ab';
        $uuid = new UUID($str);

        $this->assertEquals(hex2bin(str_replace('-', '', $str)), $uuid->toBinary());
    }

    public function testStringsAreConvertedToLowerCase()
    {
        $str = 'ABCDEF01-1234-FEDC-5678-1234567890AB';
        $lower = strtolower($str);

        $uuid1 = new UUID($str);
        $uuid2 = new UUID(str_replace('-', '', $str));
        $uuid3 = new UUID(hex2bin(str_replace('-', '', $str)));

        $this->assertEquals($lower, (string)$uuid1);
        $this->assertEquals($lower, (string)$uuid2);
        $this->assertEquals($lower, (string)$uuid3);
    }

    public function testCanConvertToJsonString()
    {
        $str = 'abcdef01-1234-fedc-5678-1234567890ab';
        $uuid = new UUID($str);

        $this->assertEquals($str, (string)$uuid);
        $this->assertEquals($str, json_decode(json_encode($uuid)));
    }

    public function testCanCopyConstruct()
    {
        $rand = UUID::createRandom();
        $uuid = new UUID($rand);

        $this->assertEquals($rand, $uuid);
        $this->assertNotSame($rand, $uuid);
    }

    public function provideInvalidUUIDs()
    {
        return [
            [''],
            ['294736t5ahaz'],
            [md5('foo') . 'bar'],
            [str_repeat('G', 32)]
        ];
    }

    /**
     * @dataProvider provideInvalidUUIDs
     * @expectedException \InvalidArgumentException
     */
    public function testDetectsInvalidUUID($str)
    {
        new UUID($str);
    }

    public function testDetectUuidVersion()
    {
        $this->assertEquals(4, UUID::createRandom()->getVersion());
    }

    public function testNameBased()
    {
        $uuid = UUID::createNameBased(new UUID(UUID::NS_DNS), 'www.example.org');

        $this->assertEquals('74738ff5-5367-5958-9aee-98fffdcd1876', (string)$uuid);
    }

    public function provideNumbers()
    {
        for ($i = 1; $i < 2000; $i++) {
            yield [$i];
        }
    }

    /**
     * @dataProvider provideNumbers
     */
    public function testRandomIdentifiers($num)
    {
        $uuid = UUID::createRandom();

        $this->assertTrue($uuid instanceof UUID);
        $this->assertTrue(preg_match(UUID::PATTERN_UUID_V4, (string)$uuid) ? true : false);
    }

    /**
     * @dataProvider provideNumbers
     */
    public function testUsingRandomGenerator($num)
    {
        $random = new RandomGenerator();
        $uuid = UUID::createRandom($random);

        $this->assertTrue($uuid instanceof UUID);
        $this->assertTrue(preg_match(UUID::PATTERN_UUID_V4, (string)$uuid) ? true : false);
    }
}
