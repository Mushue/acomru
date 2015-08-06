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
 * Bit Field using a PHP integer. There is a total of 31 usable bit flags.
 *
 * @author Martin Schröder
 */
class BitField
{
    /**
     * Contains the bits of this field combined into an integer.
     *
     * @var integer
     */
    protected $bits = 0;

    /**
     * Attempts to populate the bit flags from the given value.
     *
     * @param integer|string $bits
     */
    public function __construct($bits)
    {
        if ($bits instanceof self) {
            $this->bits = $bits->bits;
        } elseif (is_integer($bits)) {
            $this->bits = $bits;
        } else {
            $this->bits = intval(ltrim($bits, '0'), 2);
        }
    }

    /**
     * Converts the bit field into a 31-bit string.
     *
     * @return string
     */
    public function __toString()
    {
        return sprintf('%31b', $this->bits);
    }

    public function __debugInfo()
    {
        $flags = [];

        for ($i = 1; $i < pow(2, 31); $i *= 2) {
            $flags[$i] = ($this->bits & $i) ? true : false;
        }

        return [
            'binary' => sprintf('%031b', $this->bits),
            'flags' => $flags
        ];
    }

    /**
     * Returns an integer that has all appropriate bits set.
     *
     * @return integer
     */
    public function toInteger()
    {
        return $this->bits;
    }

    /**
     * Check if all of the given flag bits are set.
     *
     * @param integer $flags
     * @return boolean
     *
     * @throws \InvalidArgumentException When the given flags are not an integer.
     */
    public function has($flags)
    {
        if (!is_integer($flags)) {
            throw new \InvalidArgumentException('Flags must be an integer, given ' . gettype($flags));
        }

        return ($this->bits & $flags) == $flags;
    }

    /**
     * Check if any of the given flag bits are set.
     *
     * @param integer $flags
     * @return boolean
     *
     * @throws \InvalidArgumentException When the given flags are not an integer.
     */
    public function hasAny($flags)
    {
        if (!is_integer($flags)) {
            throw new \InvalidArgumentException('Flags must be an integer, given ' . gettype($flags));
        }

        return ($this->bits & $flags) != 0;
    }

    /**
     * Set all of the given bits to the given value (defaults to true).
     *
     * @param integer $flags
     * @param boolean $value
     * @return BitField
     *
     * @throws \InvalidArgumentException When the given flags are not an integer or are less than 0.
     */
    public function set($flags, $value = true)
    {
        if (!is_integer($flags)) {
            throw new \InvalidArgumentException('Flags must be an integer, given ' . gettype($flags));
        }

        if ($value) {
            $this->bits |= $flags;
        } else {
            $this->bits = ($this->bits | $flags) ^ $flags;
        }

        return $this;
    }

    /**
     * Clear all bits in this field by setting them to 0 / false.
     *
     * @return BitField
     */
    public function clear()
    {
        $this->bits = 0;

        return $this;
    }
}
