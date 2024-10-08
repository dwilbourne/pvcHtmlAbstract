<?php

/**
 * @author Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare(strict_types=1);

namespace pvcTests\html\abstract\tag;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use pvc\html\abstract\attribute\AttributeCustomData;
use pvc\html\abstract\attribute\AttributeVoid;
use pvc\html\abstract\attribute\Event;
use pvc\html\abstract\err\InvalidAttributeNameException;
use pvc\html\abstract\err\UnsetAttributeNameException;
use pvc\html\abstract\err\UnsetTagNameException;
use pvc\html\abstract\tag\TagVoid;
use pvc\interfaces\html\attribute\AttributeFactoryInterface;
use pvc\interfaces\html\attribute\AttributeInterface;
use pvc\interfaces\validator\ValTesterInterface;

class TagVoidTest extends TestCase
{
    /**
     * @var string
     */
    protected string $tagName;

    /**
     * @var TagVoid
     */
    protected TagVoid $tag;

    /**
     * setUp
     */
    public function setUp(): void
    {
        $this->tagName = 'a';
        $this->tag = new TagVoid();
    }

    /**
     * testSetGetTagName
     * @covers \pvc\html\abstract\tag\TagVoid::getName
     * @covers \pvc\html\abstract\tag\TagVoid::setName
     */
    public function testSetGetTagName(): void
    {
        /**
         * default behavior returns an empty string if name has not been sedt
         */
        self::assertEquals('', $this->tag->getName());
        $this->tag->setName($this->tagName);
        self::assertEquals($this->tagName, $this->tag->getName());
    }

    /**
     * testSetAllowedAttributesThrowsExceptionWithNonStringAttributeName
     * @throws InvalidAttributeNameException
     * @covers \pvc\html\abstract\tag\TagVoid::setAllowedAttributes
     */
    public function testSetAllowedAttributesThrowsExceptionWithNonStringAttributeName(): void
    {
        $allowedAttributes = ['foo', 'bar', 9];
        self::expectException(InvalidAttributeNameException::class);
        $this->tag->setAllowedAttributes($allowedAttributes);
    }

    /**
     * testSetGetAllowedAttributes
     * @covers \pvc\html\abstract\tag\TagVoid::setAllowedAttributes
     * @covers \pvc\html\abstract\tag\TagVoid::getAllowedAttributes
     */
    public function testSetGetAllowedAttributes(): void
    {
        /**
         * default is an empty array
         */
        self::assertIsArray($this->tag->getAllowedAttributes());
        self::assertEmpty($this->tag->getAllowedAttributes());

        $allowedAttributes = ['foo', 'bar', 'baz'];
        $this->tag->setAllowedAttributes($allowedAttributes);
        self::assertEqualsCanonicalizing($allowedAttributes, $this->tag->getAllowedAttributes());
    }

    /**
     * testGetAttributeReturnsNullWhenAttributeDoesNotExist
     * @covers \pvc\html\abstract\tag\TagVoid::getAttribute
     */
    public function testGetAttributeReturnsNullWhenAttributeDoesNotExist(): void
    {
        self::assertNull($this->tag->getAttribute('href'));
    }

    /**
     * setGetAttributesReturnsEmptyArrayWhenTagHasNoAttributes
     * @covers \pvc\html\abstract\tag\TagVoid::getAttributes
     */
    public function testSetGetAttributesReturnsEmptyArrayWhenTagHasNoAttributes(): void
    {
        self::assertIsArray($this->tag->getAttributes());
        self::assertEmpty($this->tag->getAttributes());
    }

    /**
     * testSetAttributeThrowsExceptionWhenAttributeNameNotSet
     * @throws UnsetAttributeNameException
     * @covers \pvc\html\abstract\tag\TagVoid::setAttribute
     */
    public function testSetAttributeThrowsExceptionWhenAttributeNameNotSet(): void
    {
        $attribute1 = $this->createMock(AttributeInterface::class);
        $attribute1->method('getName')->willReturn('');
        self::expectException(UnsetAttributeNameException::class);
        $this->tag->setAttribute($attribute1);
    }

    /**
     * testSetGetRemoveAttribute
     * @covers \pvc\html\abstract\tag\TagVoid::setAttribute
     * @covers \pvc\html\abstract\tag\TagVoid::getAttribute
     * @covers \pvc\html\abstract\tag\TagVoid::removeAttribute
     */
    public function testSetGetRemoveAttribute(): void
    {
        $attrName = 'href';

        $attribute1 = $this->createMock(AttributeInterface::class);
        $attribute1->method('getName')->willReturn($attrName);

        $attribute2 = $this->createMock(AttributeInterface::class);
        $attribute2->method('getName')->willReturn($attrName);

        self::assertNull($this->tag->getAttribute($attrName));
        $this->tag->setAttribute($attribute1);
        self::assertEquals($attribute1, $this->tag->getAttribute($attrName));

        /**
         * illustrate that you cannot have two attributes with the same name: setting the second one overwrites
         * the first
         */

        $this->tag->setAttribute($attribute2);
        $this->assertEquals(1, count($this->tag->getAttributes()));
        self::assertEquals($attribute2, $this->tag->getAttribute($attrName));

        /**
         * remove the attribute
         */
        $this->tag->removeAttribute($attrName);
        self::assertNull($this->tag->getAttribute($attrName));
        $this->assertEquals(0, count($this->tag->getAttributes()));
    }


    /**
     * testGetAttributes
     * @covers \pvc\html\abstract\tag\TagVoid::getAttributes
     */
    public function testGetAttributes(): void
    {
        $attr1Name = 'href';
        $attr1 = $this->createStub(AttributeInterface::class);
        $attr1->method('getName')->willReturn($attr1Name);

        $attr2Name = 'hidden';
        $attr2 = $this->createStub(AttributeInterface::class);
        $attr2->method('getName')->willReturn($attr2Name);

        $event1Name = 'onclick';
        $event1 = $this->createStub(Event::class);
        $event1->method('getName')->willReturn($event1Name);

        $event2Name = 'ondragstart';
        $event2 = $this->createStub(Event::class);
        $event2->method('getName')->willReturn($event2Name);

        $this->tag->setAttribute($attr1);
        $this->tag->setAttribute($attr2);
        $this->tag->setAttribute($event1);
        $this->tag->setAttribute($event2);

        /**
         * default behavior is to return both attributes and events
         */
        self::assertEquals(4, count($this->tag->getAttributes()));
        self::assertEquals(4, count($this->tag->getAttributes(TagVoid::ATTRIBUTES | TagVoid::EVENTS)));

        self::assertEquals(2, count($this->tag->getAttributes(TagVoid::ATTRIBUTES)));
        self::assertEquals(2, count($this->tag->getAttributes(TagVoid::EVENTS)));

        $this->tag->removeAttribute($event2Name);
        self::assertEquals(1, count($this->tag->getAttributes(TagVoid::EVENTS)));
    }


    /**
     * testGenerateOpeningTagWithNoTagName
     * @throws UnsetTagNameException
     * @covers \pvc\html\abstract\tag\TagVoid::generateOpeningTag
     */
    public function testGenerateOpeningTagWithNoTagName(): void
    {
        self::expectException(UnsetTagNameException::class);
        $this->tag->generateOpeningTag();
    }

    /**
     * testGenerateOpeningTagWithNoAttributes
     * @covers \pvc\html\abstract\tag\TagVoid::generateOpeningTag
     */
    public function testGenerateOpeningTagWithNoAttributes(): void
    {
        $expectedResult = '<a>';
        $this->tag->setName($this->tagName);
        self::assertEquals($expectedResult, $this->tag->generateOpeningTag());
    }

    /**
     * testGenerateOpeningTagWithAttributes
     * @covers \pvc\html\abstract\tag\TagVoid::generateOpeningTag
     */
    public function testGenerateOpeningTagWithAttributes(): void
    {
        $attr1Name = 'href';
        $attr1Value = 'bar';

        $attr1 = $this->getMockBuilder(AttributeInterface::class)
                      ->getMockForAbstractClass();
        $attr1->method('getName')->willReturn($attr1Name);
        $attr1->method('getValue')->willReturn($attr1Value);
        $attr1->method('render')->willReturn($attr1Name . '=\'' . $attr1Value . '\'');

        $event1Name = 'onclick';
        $event1Value = 'some javascript';

        $event1 = $this->getMockBuilder(AttributeInterface::class)
                       ->getMockForAbstractClass();
        $event1->method('getName')->willReturn($event1Name);
        $event1->method('getValue')->willReturn($event1Value);
        $event1->method('render')->willReturn($event1Name . '=\'' . $event1Value . '\'');

        $this->tag->setName($this->tagName);
        $this->tag->setAttribute($attr1);
        $this->tag->setAttribute($event1);

        $expectedResult = "<a href='bar' onclick='some javascript'>";
        self::assertEquals($expectedResult, $this->tag->generateOpeningTag());
    }
}