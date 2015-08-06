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
 * @author Martin Schröder
 */
abstract class Arrays
{
    public static function stripslashes(array $input)
    {
        $result = [];

        foreach ($input as $k => $v) {
            $result[$k] = is_array($v) ? self::stripslashes($v) : stripslashes($v);
        }

        return $result;
    }

    /**
     * Deep merge of arrays eventualy combining nested arrays.
     *
     * @param array $a Input array that is used as base for merging.
     * @param array $b Array that will be merged into the first array, string keys overwrite existing keys!
     * @return array The merged array.
     *
     * @throws \RuntimeException When an attempt is made to merge incompatible array types.
     */
    public static function mergeDeep(array $a, array $b)
    {
        $result = $a;

        foreach ($b as $k => $v) {
            if (is_integer($k)) {
                $result[] = $v;

                continue;
            }

            if (!array_key_exists($k, $result)) {
                $result[$k] = $v;

                continue;
            }

            if (is_array($result[$k])) {
                if (is_array($v)) {
                    $result[$k] = self::mergeDeep($result[$k], $v);

                    continue;
                }

                throw new \RuntimeException(sprintf('Incompatible array merge types: %s and %s', gettype($result[$k]), gettype($v)));
            }

            if (is_array($v)) {
                throw new \RuntimeException(sprintf('Incompatible array merge types: %s and %s', gettype($result[$k]), gettype($v)));
            }

            $result[$k] = $v;
        }

        return $result;
    }
}
