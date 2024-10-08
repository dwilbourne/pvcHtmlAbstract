<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare (strict_types=1);

namespace pvcTests\html\abstract\attribute;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use pvc\html\abstract\attribute\AttributeVoid;
use pvc\html\abstract\err\InvalidAttributeNameException;
use pvc\html\abstract\err\InvalidAttributeValueException;
use pvc\html\abstract\err\UnsetAttributeNameException;
use pvc\interfaces\validator\ValTesterInterface;

class AttributeVoidTest extends TestCase
{
    protected string $name;

    protected ValTesterInterface|MockObject $tester;

    protected AttributeVoid $attribute;

    public function setUp(): void
    {
        $this->name = 'hidden';
        $this->tester = $this->createMock(ValTesterInterface::class);
        $this->attribute = new AttributeVoid($this->name, $this->tester);
    }

    /**
     * testSetAttributeThrowsExceptionWithVoidAttributeNameAndNonBooleanValue
     * @throws InvalidAttributeValueException
     * @covers \pvc\html\abstract\attribute\AttributeVoid::setValue
     */
    public function testSetAttributeThrowsExceptionWithVoidAttributeNameAndNonBooleanValue(): void
    {
        self::expectException(InvalidAttributeValueException::class);
        $value = 'foobar';
        $this->attribute->setValue($value);
    }


    /**
     * testSetGetValue
     * @covers \pvc\html\abstract\attribute\AttributeVoid::setValue
     * @covers \pvc\html\abstract\attribute\AttributeVoid::getValue
     */
    public function testSetGetValue(): void
    {
        $this->tester->method('testValue')->willReturn(true);
        self::assertTrue($this->attribute->getValue());
        $this->attribute->setValue(false);
        self::assertFalse($this->attribute->getValue());
    }

    /**
     * testRenderReturnsAttributeNameWhenUsageValueToTrue
     * @covers \pvc\html\abstract\attribute\AttributeVoid::render
     */
    public function testRenderReturnsAttributeNameWhenUsageValueToTrue(): void
    {
        $expectedOutput = $this->name;
        self::assertTrue($this->attribute->getValue());
        self::assertEquals($expectedOutput, $this->attribute->render());
    }

    /**
     * testRenderReturnsEmptyStringWhenValueSetToFalse
     * @throws UnsetAttributeNameException
     * @throws InvalidAttributeNameException
     * @covers \pvc\html\abstract\attribute\AttributeVoid::render
     */
    public function testRenderReturnsEmptyStringWhenValueSetToFalse(): void
    {
        $expectedOutput = '';
        $this->tester->method('testValue')->willReturn(true);
        $this->attribute->setValue(false);
        self::assertEquals($expectedOutput, $this->attribute->render());
    }
}
