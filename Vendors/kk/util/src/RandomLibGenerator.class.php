<?php

/*
 * This file is part of KoolKode Util.
 *
 * (c) Martin Schröder <m.schroeder2007@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KoolKode\Util;

use RandomLib\Factory;
use SecurityLib\Strength;

/**
 * Random generator that uses <b>RamdomLib</b> created by <b>Anthony Ferrara</b>.
 *
 * @link https://github.com/ircmaxell/RandomLib
 *
 * @author Martin Schröder
 */
class RandomLibGenerator extends RandomGenerator
{
    protected $factory;

    public function __construct($hash = 'sha512')
    {
        parent::__construct($hash);

        $this->factory = new Factory();
    }

    public function getFactory()
    {
        return $this->factory;
    }

    public function generateInt($min = 0, $max = PHP_INT_MAX, $strength = self::STRENGTH_MEDIUM)
    {
        $generator = $this->factory->getGenerator($this->convertStrength($strength));

        return $generator->generateInt($min, $max);
    }

    protected function convertStrength($strength)
    {
        $strength = (int)$strength;

        if ($strength > self::STRENGTH_MEDIUM) {
            return new Strength(Strength::HIGH);
        }

        if ($strength > self::STRENGTH_LOW) {
            return new Strength(Strength::MEDIUM);
        }

        if ($strength > self::STRENGTH_VERY_LOW) {
            return new Strength(Strength::LOW);
        }

        return new Strength(Strength::VERYLOW);
    }

    protected function generateRandom($length, $strength)
    {
        $generator = $this->factory->getGenerator($this->convertStrength($strength));

        return $generator->generate($length);
    }
}
