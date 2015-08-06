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
 * @covers \KoolKode\Util\RandomGenerator
 */
class RandomGeneratorTest extends \PHPUnit_Framework_TestCase
{
    public function testGenerationOfRawBytes()
    {
        $random = new RandomGenerator();
        $nonce = $random->generateRaw(16);

        $this->assertEquals(16, strlen($nonce));
    }

    public function testGenerationOfHexStrings()
    {
        $random = new RandomGenerator();
        $nonce = $random->generateHexString(16);

        $this->assertEquals(32, strlen($nonce));
        $this->assertTrue(preg_match("'^[0-9a-f]+$'i", $nonce) ? true : false);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testDetectsUnsupportedAlgorithm()
    {
        new RandomGenerator('K@#%!');
    }
}
