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

use RandomLib\Factory;

/**
 * @covers \KoolKode\Util\RandomLibGenerator
 */
class RandomLibGeneratorTest extends \PHPUnit_Framework_TestCase
{
    public function testCanAccessSharedFactory()
    {
        $random = new RandomLibGenerator();
        $factory = $random->getFactory();

        $this->assertInstanceOf(Factory::class, $factory);
        $this->assertSame($factory, $random->getFactory());
    }

    public function testGenerationOfRawBytes()
    {
        $random = new RandomLibGenerator();
        $nonce = $random->generateRaw(16, RandomLibGenerator::STRENGTH_VERY_LOW);

        $this->assertEquals(16, strlen($nonce));
    }

    public function testGenerationOfHexStrings()
    {
        $random = new RandomLibGenerator();
        $nonce = $random->generateHexString(16);

        $this->assertEquals(32, strlen($nonce));
        $this->assertTrue(preg_match("'^[0-9a-f]+$'i", $nonce) ? true : false);
    }

    public function testCanGenerateRandomInt()
    {
        $random = new RandomLibGenerator();
        $lower = 234;
        $upper = 383445;
        $number = $random->generateInt($upper, $lower, RandomLibGenerator::STRENGTH_LOW);

        $this->assertTrue($number >= $lower);
        $this->assertTrue($number <= $upper);
    }
}
