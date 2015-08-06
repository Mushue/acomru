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
 * Provides a node to be used in topsort.
 *
 * @author Martin Schröder
 */
class TopSortNode
{
    public $key;

    public $edges = [];

    public $payload;

    public function __construct($key, array $edges = [], $payload = NULL)
    {
        $this->key = (string)$key;
        $this->payload = $payload;

        foreach ($edges as $k => $v) {
            if (is_string($k)) {
                $this->edges[$k] = true;
            } else {
                $this->edges[$v] = true;
            }
        }
    }
}
