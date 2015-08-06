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
 * Provides a graph tailored to topsort.
 *
 * @author Martin Schröder
 */
class TopSort
{
    protected $graph = [];

    public function addNode(TopSortNode $node)
    {
        $this->graph[$node->key] = $node;
    }

    public function sortNodes()
    {
        $graph = [];

        foreach ($this->graph as $k => $v) {
            $graph[$k] = clone $v;
        }

        // Remove edges pointing to external nodes.
        foreach ($graph as $node) {
            foreach (array_keys($node->edges) as $target) {
                if (empty($graph[$target])) {
                    unset($node->edges[$target]);
                }
            }
        }

        // Collect start nodes for topsort.
        $sorted = [];
        $rooted = [];

        foreach ($graph as $node) {
            if (empty($node->edges)) {
                $rooted[] = $node;
            }
        }

        // Perform topsort of nodes.
        while (!empty($rooted)) {
            $n = $sorted[] = array_shift($rooted);

            foreach ($graph as $m) {
                if (isset($m->edges[$n->key])) {
                    unset($m->edges[$n->key]);

                    if (empty($m->edges)) {
                        $rooted[] = $m;
                    }
                }
            }
        }

        return $sorted;
    }
}
