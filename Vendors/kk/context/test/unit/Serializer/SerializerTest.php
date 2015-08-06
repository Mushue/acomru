<?php

/*
 * This file is part of KoolKode Context and Dependency Injection.
*
* (c) Martin Schröder <m.schroeder2007@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace KoolKode\Context\Serializer;

use KoolKode\Context\Container;
use KoolKode\Context\Scope\ScopedProxyMock;

/**
 * @covers \KoolKode\Context\Serializer\Serializer
 * @covers \KoolKode\Context\Serializer\Reviver
 */
class SerializerTest extends \PHPUnit_Framework_TestCase
{
    public function provideSerializerOptions()
    {
        $options = [[false]];

        if (method_exists('Closure', 'bind')) {
            $options[] = [true];
        }

        return $options;
    }

    /**
     * @dataProvider provideSerializerOptions
     */
    public function testCanSerializeObjectWithPublicCustomProperties($bind)
    {
        $container = new Container();
        $serializer = new Serializer($container);
        $serializer->setBindClosure($bind);

        $test = new \stdClass();
        $test->foo = 'bar';
        $test->hello = 'world';

        $spec = $serializer->serialize($test);
        $this->assertEquals(serialize($test), $spec);
        $this->assertEquals($test, unserialize($spec));
    }

    /**
     * @dataProvider provideSerializerOptions
     */
    public function testCanSerializeReferencesToObjects($bind)
    {
        $container = new Container();
        $serializer = new Serializer($container);
        $serializer->setBindClosure($bind);

        $bar = new \stdClass();

        $test = new \stdClass();
        $test->hello = 'world';
        $test->foo = $bar;
        $test->tags = ['my', 'serializer'];
        $test->baz = $bar;

        $spec = $serializer->serialize($test);
        $this->assertEquals(serialize($test), $spec);
        $this->assertEquals($test, unserialize($spec));
    }

    /**
     * @dataProvider provideSerializerOptions
     */
    public function testScopedProxyPropertiesRevived($bind)
    {
        $container = new Container();
        $serializer = new Serializer($container);
        $serializer->setBindClosure($bind);

        $bindingName = 'MyTestBinding';
        $scoped = new ScopedProxyMock($bindingName);

        $container->bindInstance($bindingName, $scoped);

        $test = new \stdClass();
        $test->hello = 'world';
        $test->lazy = $scoped;

        $spec = $serializer->serialize($test);
        $obj = $serializer->unserialize($spec);

        $this->assertTrue($obj instanceof \stdClass);
        $this->assertEquals($test->hello, $obj->hello);
        $this->assertSame($scoped, $obj->lazy);
    }

    /**
     * @dataProvider provideSerializerOptions
     */
    public function testSerializerRespectsSerializable($bind)
    {
        $dummy = new SerializableDummy('foo and bar');

        $serializer = new Serializer(new Container());
        $serializer->setBindClosure($bind);

        $spec = $serializer->serialize($dummy);
        $obj = $serializer->unserialize($spec);

        $this->assertFalse($dummy->revived);
        $this->assertEquals($dummy->title, $obj->title);
        $this->assertTrue($obj->revived);
    }

    /**
     * @dataProvider provideSerializerOptions
     */
    public function testCanSerializeObjectImplementingSleep($bind)
    {
        $dummy = new SleepDummy('foo', 'bar', 'baz', 'booom');

        $serializer = new Serializer(new Container());
        $serializer->setBindClosure($bind);

        $spec = $serializer->serialize($dummy);
        $obj = $serializer->unserialize($spec);

        $this->assertEquals('foo', $obj->a);
        $this->assertEquals('bar', $obj->getB());
        $this->assertEquals('baz', $obj->getC());
        $this->assertNull($obj->d);
    }

    /**
     * @dataProvider provideSerializerOptions
     * @expectedException \KoolKode\Context\Serializer\SerializationException
     */
    public function testSerializingObjectImplementingSleepReturningInvalidFieldName($bind)
    {
        (new Serializer(new Container(), $bind))->serialize(new SleepDummy2());
    }

    /**
     * @dataProvider provideSerializerOptions
     * @expectedException \KoolKode\Context\Serializer\SerializationException
     */
    public function testSerializingClosureTriggersException($bind)
    {
        $serializer = new Serializer(new Container(), $bind);
        $serializer->serialize(function () {
        });
    }

    /**
     * @dataProvider provideSerializerOptions
     * @expectedException \KoolKode\Context\Serializer\UnserializationException
     */
    public function testCannotUnserializeInvalidData($bind)
    {
        (new Serializer(new Container(), $bind))->unserialize(':!§(§');
    }

    /**
     * @dataProvider provideSerializerOptions
     * @expectedException \KoolKode\Context\Serializer\UnserializationException
     */
    public function testCannotUnserializeReviverWithoutSerializer($bind)
    {
        $serializer = new Serializer(new Container());
        $serializer->setBindClosure($bind);

        $test = new \stdClass();
        $test->tags = ['hello', 'world'];
        $test->test = new ScopedProxyMock('MyTestBinding');

        unserialize($serializer->serialize($test));
    }

    /**
     * @dataProvider provideSerializerOptions
     */
    public function testCanSerializePrivateInjectedProperty($bind)
    {
        $test = new \stdClass();
        $test->message = 'Hello';

        $container = new Container();
        $container->bindInstance('stdClass', $test);

        $serializer = new Serializer($container);
        $serializer->setBindClosure($bind);

        $challenge = new SerializerChallenge($test);
        $result = $serializer->unserialize($serializer->serialize($challenge));
        $this->assertTrue($result instanceof SerializerChallenge);
        $this->assertEquals($challenge, $result);
        $this->assertNotSame($challenge, $result);
        $this->assertSame($test, $result->getFoo());
    }
}
