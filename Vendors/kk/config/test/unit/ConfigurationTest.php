<?php

/*
 * This file is part of KoolKode Config.
*
* (c) Martin Schröder <m.schroeder2007@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace KoolKode\Config;

/**
 * @covers KoolKode\Config\Configuration
 */
class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    public function testCanCreateConfigUsingConstructor()
    {
        $config = new Configuration();
        $config2 = new Configuration(['hello' => 'world', 'foo' => 'bar']);
        $config3 = new Configuration($config2);

        $this->assertEquals([], $config->toArray());
        $this->assertEquals(['foo' => 'bar', 'hello' => 'world'], $config2->toArray());
        $this->assertEquals(['foo' => 'bar', 'hello' => 'world'], $config3->toArray());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testConstructorThrowsExceptionOnInvalidData()
    {
        new Configuration('foobar');
    }

    public function testCanAccessContainedData()
    {
        $config = new Configuration(['title' => 'Test', 'tags' => ['hello', 'world']]);

        $this->assertTrue($config->has('title'));
        $this->assertTrue($config->has('tags'));
        $this->assertFalse($config->has('test'));

        $this->assertEquals(['hello', 'world'], $config->get('tags'));
        $this->assertEquals('bla', $config->get('foo', 'bla'));
    }

    /**
     * @expectedException \OutOfBoundsException
     */
    public function testThrowsExceptionWhenValueIsNotFound()
    {
        (new Configuration())->get('my.foo');
    }

    public function testCanSetStateOfConfig()
    {
        $data = ['foo' => 'bar'];
        $config = Configuration::__set_state(['data' => $data]);

        $this->assertTrue($config instanceof Configuration);
        $this->assertEquals($data, $config->toArray());
    }

    public function testCanIterateOverConfig()
    {
        $data = ['foo' => 'bar', 'hello' => 'world'];
        $config = new Configuration($data);

        $this->assertEquals($data, iterator_to_array($config->getIterator()));
    }

    public function testCanSerializeConfig()
    {
        $data = [
            'settings' => [
                'title' => 'Hello World',
                'public' => true,
                'entries' => 8
            ]
        ];
        $c1 = new Configuration($data);
        $c2 = unserialize(serialize($c1));
        $json = json_encode($data);

        $this->assertEquals($c1, $c2);
        $this->assertNotSame($c1, $c2);
        $this->assertEquals(json_decode($json, true), $c1->jsonSerialize());
    }

    public function testCanDumpYamlStringFromSortedConfig()
    {
        $config = new Configuration([
            'title' => 'Test',
            'public' => false,
            'tags' => [
                'hello',
                'world'
            ],
            'x' => 245
        ]);
        $config->sortByKey();

        $yaml = "public: false\n";
        $yaml .= "tags:\n";
        $yaml .= "  0: \"hello\"\n";
        $yaml .= "  1: \"world\"\n";
        $yaml .= "title: \"Test\"\n";
        $yaml .= "x: 245\n";

        $this->assertEquals($yaml, $config->toString());
        $this->assertEquals($yaml, (string)$config);
    }

    public function testCanAccessBooleanValues()
    {
        $config = new Configuration(['test' => ['enabled' => true, 'cache' => false]]);

        $this->assertTrue($config->getBoolean('test.enabled'));
        $this->assertFalse($config->getBoolean('test.cache'));
        $this->assertTrue($config->getBoolean('test.validate', true));
    }

    /**
     * @expectedException \OutOfBoundsException
     */
    public function testThrowsExceptionWhenBooleanValueIsNotFound()
    {
        (new Configuration())->getBoolean('my.foo');
    }

    public function testCanAccessIntegerValues()
    {
        $config = new Configuration(['foo' => 13, 'bar' => -297]);

        $this->assertEquals(13, $config->getInteger('foo'));
        $this->assertEquals(-297, $config->getInteger('bar'));
        $this->assertEquals(1337, $config->getInteger('foo.bar', 1337));
    }

    /**
     * @expectedException \OutOfBoundsException
     */
    public function testThrowsExceptionWhenIntegerValueIsNotFound()
    {
        (new Configuration())->getInteger('bar');
    }

    public function testCanAccessFloatValues()
    {
        $config = new Configuration(['foo' => 12.347, 'bar' => -297.23]);

        $this->assertEquals(12.347, $config->getFloat('foo'));
        $this->assertEquals(-297.23, $config->getFloat('bar'));
        $this->assertEquals(12.0, $config->getFloat('foo.bar', 12));
    }

    /**
     * @expectedException \OutOfBoundsException
     */
    public function testThrowsExceptionWhenFloatValueIsNotFound()
    {
        (new Configuration())->getFloat('bar');
    }

    public function testCanAccessStringValues()
    {
        $config = new Configuration(['title' => 'hello world', 'label' => 'Test']);

        $this->assertEquals('hello world', $config->getString('title'));
        $this->assertEquals('Test', $config->getString('label'));
        $this->assertEquals('dummy', $config->getString('foo.bar', 'dummy'));
    }

    /**
     * @expectedException \OutOfBoundsException
     */
    public function testThrowsExceptionWhenStringValueIsNotFound()
    {
        (new Configuration())->getString('bar');
    }

    public function testCanAccessArrayValues()
    {
        $config = new Configuration(['foo' => 'bar', 'tags' => ['hello', 'world']]);

        $this->assertEquals(['bar'], $config->getArray('foo'));
        $this->assertEquals(['hello', 'world'], $config->getArray('tags'));
        $this->assertEquals([], $config->getArray('my.dummy'));
    }

    public function testCanCountConfigEntries()
    {
        $config = new Configuration(['foo' => 'bar', 'tags' => ['hello', 'world']]);

        $this->assertEquals(0, $config->getCount('my.foo'));
        $this->assertEquals(1, $config->getCount('foo'));
        $this->assertEquals(2, $config->getCount('tags'));
    }

    public function testCanAccessConfigObject()
    {
        $config = new Configuration(['foo' => 'bar', 'tags' => ['hello', 'world']]);

        $this->assertEquals(['hello', 'world'], $config->getConfig('tags')->toArray());
        $this->assertEquals(['title' => 'Test'], $config->getConfig('na', new Configuration(['title' => 'Test']))->toArray());
    }

    public function provideMergeArrays()
    {
        return [
            [[], [], []],
            [['foo' => 'bar'], ['bam' => 'baz'], ['foo' => 'bar', 'bam' => 'baz']],
            [['foo' => 'bar'], ['foo' => 'baz'], ['foo' => 'baz']],
            [['tags' => ['hello']], ['tags' => ['world', 'universe']], ['tags' => ['hello', 'world', 'universe']]],
            [['tags' => []], ['tags' => ['hello', 'world']], ['tags' => ['hello', 'world']]],
            [['tags' => ['hello', 'world']], ['tags' => []], ['tags' => ['hello', 'world']]],
            [['info' => ['message' => 'hello']], ['info' => ['message' => 'world']], ['info' => ['message' => 'world']]],
            [['tags' => ['hello']], ['tags' => ['world']], ['tags' => ['hello', 'world']]]
        ];
    }

    /**
     * @dataProvider provideMergeArrays
     */
    public function testCanMergeConfigObjects(array $a1, array $a2, array $result)
    {
        $config1 = new Configuration($a1);
        $config2 = new Configuration($a2);
        $config3 = $config1->mergeWith($config2);

        $this->assertEquals($result, $config3->toArray());
    }

    public function provideFailingMergeArrays()
    {
        return [
            [['foo' => 'bar'], ['foo' => []]],
            [['foo' => []], ['foo' => 'bar']],
            [['tags' => ['foo']], ['tags' => ['foo' => 134]]],
        ];
    }

    /**
     * @dataProvider provideFailingMergeArrays
     * @expectedException \KoolKode\Config\ConfigurationMergeException
     */
    public function testMergingOfIncompatibleConfigsFails(array $a1, array $a2)
    {
        (new Configuration($a1))->mergeWith(new Configuration($a2));
    }

    public function testCanIterateOverScalarValues()
    {
        $it = (new Configuration(['foo' => 'bar', 'seed' => 1337]))->getIterator();

        $it->rewind();
        $this->assertTrue($it->valid());
        $this->assertEquals('foo', $it->key());
        $this->assertEquals('bar', $it->current());

        $it->next();
        $this->assertTrue($it->valid());
        $this->assertEquals('seed', $it->key());
        $this->assertEquals(1337, $it->current());

        $it->next();
        $this->assertFalse($it->valid());
    }

    public function testWrapsArrayValuesInConfigObjects()
    {
        $it = (new Configuration(['title' => 'News', 'tags' => ['hello', 'world']]))->getIterator();

        $it->rewind();
        $this->assertTrue($it->valid());
        $this->assertEquals('title', $it->key());
        $this->assertEquals('News', $it->current());

        $it->next();
        $this->assertTrue($it->valid());
        $this->assertEquals('tags', $it->key());

        $config = $it->current();
        $this->assertTrue($config instanceof Configuration);
        $this->assertEquals(['hello', 'world'], $config->toArray());

        $it->next();
        $this->assertFalse($it->valid());
    }

    public function testConvertsEmptyArraysIntoEmptyConfigs()
    {
        $it = (new Configuration(['tags' => []]))->getIterator();

        $it->rewind();
        $this->assertTrue($it->valid());
        $this->assertEquals('tags', $it->key());

        $config = $it->current();
        $this->assertTrue($config instanceof Configuration);
        $this->assertEquals([], $config->toArray());

        $it->next();
        $this->assertFalse($it->valid());
    }
}
