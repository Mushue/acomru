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
 * @covers \KoolKode\Util\Arrays
 */
class ArraysTest extends \PHPUnit_Framework_TestCase
{
    public function provideSlashArrays()
    {
        return [
            [[], []],
            [['a'], ['a']],
            [['my \\\' foo'], ['my \' foo']],
            [['double \\\\ backslash'], ['double \\ backslash']],
            [['foo' => ['bar', '\\"baz\\"']], ['foo' => ['bar', '"baz"']]],
            [['tags' => ['\\\'hello\\\'', '\"world?\"']], ['tags' => ['\'hello\'', '"world?"']]]
        ];
    }

    /**
     * @dataProvider provideSlashArrays
     */
    public function testCanStripSlashesDeep($a, $b)
    {
        $this->assertEquals($b, Arrays::stripslashes($a));
    }

    public function provideMergeArrays()
    {
        return [
            [[], [], []],
            [[23, 4], [], [23, 4]],
            [[], [34, 2], [34, 2]],
            [['foo' => []], [], ['foo' => []]],
            [[], ['bar' => 'b'], ['bar' => 'b']],
            [[24], ['foo' => 'bar'], [24, 'foo' => 'bar']],
            [[54, 'foo' => []], [24], [54, 24, 'foo' => []]],
            [[23, 43], [23, 12], [23, 43, 23, 12]],
            [['foo' => 'bar'], ['bam' => 'baz'], ['foo' => 'bar', 'bam' => 'baz']],
            [['foo' => 'x'], ['foo' => 'y'], ['foo' => 'y']],
            [['tags' => ['hello', 'world']], ['tags' => ['universe']], ['tags' => ['hello', 'world', 'universe']]],
            [['a' => ['b' => 'c']], ['a' => ['b' => 'd']], ['a' => ['b' => 'd']]],
            [['a' => ['b' => 'c']], ['a' => ['c' => 'd']], ['a' => ['b' => 'c', 'c' => 'd']]]
        ];
    }

    /**
     * @dataProvider provideMergeArrays
     */
    public function testCanMergeDeepArrays($a, $b, $c)
    {
        $this->assertEquals($c, Arrays::mergeDeep($a, $b));
    }

    public function provideUnmergeableArrays()
    {
        return [
            [['tags' => 'my, tags'], ['tags' => ['my', 'tags']]],
            [['tags' => ['my', 'tags']], ['tags' => 'my, tags']]
        ];
    }

    /**
     * @dataProvider provideUnmergeableArrays
     * @expectedException \RuntimeException
     */
    public function testCannotMergeSomeArrays(array $a, array $b)
    {
        Arrays::mergeDeep($a, $b);
    }
}
