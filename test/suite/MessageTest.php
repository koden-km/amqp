<?php
namespace Recoil\Amqp;

use PHPUnit_Framework_TestCase;

class MessageTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->amqpProperties = Attributes::create([
            'a' => '1',
            'b' => '2',
        ]);

        $this->customProperties = Attributes::create([
            'c' => '3',
            'd' => '4',
        ]);

        $this->subject = Message::create(
            '<payload>',
            $this->amqpProperties,
            $this->customProperties
        );
    }

    public function testCreateWithArrays()
    {
        $subject = Message::create(
            '<payload>',
            iterator_to_array($this->amqpProperties),
            iterator_to_array($this->customProperties)
        );

        $this->assertEquals(
            $this->subject,
            $subject
        );
    }

    public function testPayload()
    {
        $this->assertSame(
            '<payload>',
            $this->subject->payload()
        );
    }

    public function testAmqpProperties()
    {
        $this->assertSame(
            $this->amqpProperties,
            $this->subject->amqpProperties()
        );
    }

    public function testCustomProperties()
    {
        $this->assertSame(
            $this->customProperties,
            $this->subject->customProperties()
        );
    }
}
