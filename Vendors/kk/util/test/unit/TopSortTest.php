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
 * @covers \KoolKode\Util\TopSort
 * @covers \KoolKode\Util\TopSortNode
 */
class TopSortTest extends \PHPUnit_Framework_TestCase
{
    public function testWillSortGraphWithoutCycle()
    {
        $sort = new TopSort();

        $sort->addNode(new TopSortNode('C', ['A']));
        $sort->addNode(new TopSortNode('D', ['C' => true, 'X']));
        $sort->addNode(new TopSortNode('A'));
        $sort->addNode(new TopSortNode('E', ['B', 'D']));
        $sort->addNode(new TopSortNode('F', ['E']));
        $sort->addNode(new TopSortNode('B', ['A', 'Y']));

        $sorted = $sort->sortNodes();
        $this->assertCount(6, $sorted);
        $this->assertEquals('A', $sorted[0]->key);
        $this->assertEquals('E', $sorted[4]->key);
        $this->assertEquals('F', $sorted[5]->key);
    }
}
