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
 * @covers \KoolKode\Util\MediaType
 */
class MediaTypeTest extends \PHPUnit_Framework_TestCase
{
    public function provideMediaTypeStrings()
    {
        return [
            ['application/xml', 'application', ['xml']],
            ['application/xhtml+xml', 'application', ['xhtml', 'xml']],
            ['image/*', 'image', ['*']],
        ];
    }

    /**
     * @dataProvider provideMediaTypeStrings
     */
    public function testCanParseMediaTypes($input, $type, array $sub)
    {
        $mediaType = new MediaType($type, $sub);
        $this->assertEquals($type, $mediaType->getType());
        $this->assertEquals($sub, $mediaType->getSubTypes());
    }

    public function testCanCopyConstruct()
    {
        $type1 = new MediaType('application/xhtml+xml');
        $type2 = new MediaType($type1);

        $this->assertNotSame($type1, $type2);
        $this->assertEquals($type1, $type2);
    }

    /**
     * @expectedException \KoolKode\Util\InvalidMediaTypeException
     */
    public function testWilNotAcceptEmptyType()
    {
        new MediaType('', ['json']);
    }

    public function testWillRemoveEmptySubTypes()
    {
        $type = new MediaType('application/xml+');
        $this->assertEquals(['xml'], $type->getSubTypes());
    }

    /**
     * @expectedException \KoolKode\Util\InvalidMediaTypeException
     */
    public function testAtLeastOneSubTypeIsRequired()
    {
        new MediaType('application', []);
    }

    /**
     * @expectedException \KoolKode\Util\InvalidMediaTypeException
     */
    public function testDoesNotAllowAdditionalSubTypesBesidesWildcard()
    {
        new MediaType('application/*+xml');
    }

    public function provideCheckTypes()
    {
        return [
            ['text/css', 'text/css', true],
            ['text/css', 'text/*', true],
            ['text/*', 'text/*', true],
            ['text/css', 'text/html', false],
            ['text/html+css', 'text/html', true],
            ['text/html+css', 'text/css', true],
            ['text/html+css', 'text/javascript', false],
            ['text/css', 'application/css', false],
            ['text/css', '*/css', true],
            ['text/html+css', '*/css', true],
            ['text/html+css', '*/html', true],
            ['text/html+css', 'text/html+css', true],
        ];
    }

    /**
     * @dataProvider provideCheckTypes
     */
    public function testCanCheckTypesAgainstOtherTypes($type1, $type2, $expected)
    {
        $this->assertEquals($expected, (new MediaType($type1))->is($type2));
    }

    public function testDetectsWildcardTypes()
    {
        $type1 = new MediaType('image/*');
        $type2 = new MediaType('*/xml');
        $type3 = new MediaType('*/*');

        $this->assertTrue($type1->isWildcardType());
        $this->assertTrue($type2->isWildcardType());
        $this->assertTrue($type3->isWildcardType());
    }

    public function testCanIterateOverMediaSubTypes()
    {
        $type = new MediaType('application/rdf+xml');
        $this->assertCount(2, $type);

        $it = $type->getIterator();

        $it->rewind();
        $this->assertTrue($it->valid());
        $this->assertEquals(0, $it->key());
        $this->assertTrue($it->current() instanceof MediaType);
        $this->assertEquals('application/rdf', (string)$it->current());
        $this->assertEquals('application/rdf', $it->current()->jsonSerialize());

        $it->next();
        $this->assertTrue($it->valid());
        $this->assertEquals(1, $it->key());
        $this->assertTrue($it->current() instanceof MediaType);
        $this->assertEquals('application/xml', (string)$it->current());
        $this->assertEquals('application/xml', $it->current()->jsonSerialize());

        $it->next();
        $this->assertFalse($it->valid());
    }

    public function provideTypesForChecking()
    {
        return [
            ['application/xml', true, false, false, false, false, false],
            ['audio/wav', false, true, false, false, false, false],
            ['image/jpeg', false, false, true, false, false, false],
            ['multipart/form-data', false, false, false, true, false, false],
            ['text/plain', false, false, false, false, true, false],
            ['video/avi', false, false, false, false, false, true],
        ];
    }

    /**
     * @dataProvider provideTypesForChecking
     */
    public function testCanCheckTypes($input, $app, $audio, $img, $multi, $text, $video)
    {
        $type = new MediaType($input);
        $this->assertEquals($app, $type->isApplication());
        $this->assertEquals($audio, $type->isAudio());
        $this->assertEquals($img, $type->isImage());
        $this->assertEquals($multi, $type->isMultipart());
        $this->assertEquals($text, $type->isText());
        $this->assertEquals($video, $type->isVideo());
    }

    public function testWillDetectNonWildcardType()
    {
        $this->assertFalse((new MediaType('application/xml'))->isWildcardType());
    }

    public function provideTypes()
    {
        return [
            ['application/xml', 'application', true],
            ['application/xml', 'text', false],
            ['application/xml', '*', true],
            ['*/xml', 'application', true],
            ['*/*', 'image', true]
        ];
    }

    /**
     * @dataProvider provideTypes
     */
    public function testCanCheckType($input, $check, $result)
    {
        $type = new MediaType($input);
        $this->assertEquals($result, $type->isType($check));
    }

    public function provideSubTypes()
    {
        return [
            ['application/xml', 'xml', true],
            ['application/json', 'xml', false],
            ['application/xml', '*', true],
            ['application/*', 'xml', true]
        ];
    }

    /**
     * @dataProvider provideSubTypes
     */
    public function testCanCheckSubType($input, $check, $result)
    {
        $type = new MediaType($input);
        $this->assertEquals($result, $type->isSubType($check));
    }
}
