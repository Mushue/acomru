<?php

/*
 * This file is part of KoolKode Util.
*
* (c) Martin Schröder <m.schroeder2007@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

// TODO: Implement generation of random integers.

namespace KoolKode\Util;

/**
 * Generator for ramdom byte strings of specific length (ramdom integers TBD).
 *
 * @author Martin Schröder
 */
interface RandomGeneratorInterface
{
    /**
     * This represents Non-Cryptographic strengths. It should not be used any time
     * that security or confidentiality is at stake.
     *
     * @var integer
     */
    const STRENGTH_VERY_LOW = 0;

    /**
     * This represents the bottom line of Cryptographic strengths. It may be used
     * for low security uses where some strength is required.
     *
     * @var integer
     */
    const STRENGTH_LOW = 100;

    /**
     * This is the general purpose Cryptographical strength. It should be suitable
     * for all uses except the most sensitive.
     *
     * @var integer
     */
    const STRENGTH_MEDIUM = 200;

    /**
     * This is the highest strength available. It should not be used unless the
     * high strength is needed, due to hardware constraints (and entropy limitations).
     *
     * @var integer
     */
    const STRENGTH_HIGH = 300;

    /**
     * Generate a string of hex byte values (the string will contain <b>$length * 2</b> characters!).
     *
     * @param integer $length The number of random bytes to be generated.
     * @param integer $strength The strength of the generator, one of the RandomGeneratorInterface::STRENGTH_* constants.
     * @return string
     */
    public function generateHexString($length, $strength = self::STRENGTH_MEDIUM);

    /**
     * Generate a string of raw random bytes.
     *
     * @param integer $length The number of random bytes to be generated.
     * @param integer $strength The strength of the generator, one of the RandomGeneratorInterface::STRENGTH_* constants.
     * @return string
     */
    public function generateRaw($length, $strength = self::STRENGTH_MEDIUM);
}
