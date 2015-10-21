<?php

namespace Recoil\Amqp\v091\Protocol;

use PHPUnit_Framework_TestCase;
use Recoil\Amqp\Exception\ProtocolException;

class SigTableParserTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->subject = new SigTableParser();
    }

    public function parseTestVectors()
    {
        yield 'empty' => [
            "\x00\x00\x00\x00",
            [],
        ];

        yield 'boolean (t)' => [
            "\x00\x00\x00\x0f"
            . "\x04true" . "t\x01"
            . "\x05false" . "t\x00",
            [
                'true' => true,
                'false' => false,
            ],
        ];

        yield 'signed octet (b)' => [
            "\x00\x00\x00\x06" . "\x03key" . "b\xfe",
            ['key' => -2],
        ];

        yield 'signed short (s)' => [
            "\x00\x00\x00\x07" . "\x03key" . "s\xff\xfe",
            ['key' => -2],
        ];

        yield 'signed long (I)' => [
            "\x00\x00\x00\x09" . "\x03key" . "I\xff\xff\xff\xfe",
            ['key' => -2],
        ];

        yield 'signed long long (l)' => [
            "\x00\x00\x00\x0d" . "\x03key" . "l\xff\xff\xff\xff\xff\xff\xff\xfe",
            ['key' => -2],
        ];

        yield 'float (f)' => [
            "\x00\x00\x00\x09"
            . "\x03key" . "f\x40\x49\x0e\x56",
            ['key' => 3.1414999961853027],
        ];

        yield 'double (d)' => [
            "\x00\x00\x00\x0d"
            . "\x03key" . "d\x40\x09\x21\xCA\xC0\x83\x12\x6F",
            ['key' => 3.14150000000000018118839761883],
        ];

        yield 'decimal - pos (D)' => [
            "\x00\x00\x00\x0a" . "\x03key" . "D\x02\x00\x00\x01\x3a",
            ['key' => '3.14'],
        ];

        yield 'decimal - pos - zero scale (D)' => [
            "\x00\x00\x00\x0a" . "\x03key" . "D\x00\x00\x00\x01\x3a",
            ['key' => '314'],
        ];

        yield 'decimal - pos - scale larger than string representation (D)' => [
            "\x00\x00\x00\x0a" . "\x03key" . "D\x05\x00\x00\x01\x3a",
            ['key' => '0.00314'],
        ];

        yield 'decimal - neg (D)' => [
            "\x00\x00\x00\x0a" . "\x03key" . "D\x02\xff\xff\xfe\xc6",
            ['key' => '-3.14'],
        ];

        yield 'decimal - neg - zero scale (D)' => [
            "\x00\x00\x00\x0a" . "\x03key" . "D\x00\xff\xff\xfe\xc6",
            ['key' => '-314'],
        ];

        yield 'decimal - neg - scale larger than string representation (D)' => [
            "\x00\x00\x00\x0a" . "\x03key" . "D\x05\xff\xff\xfe\xc6",
            ['key' => '-0.00314'],
        ];

        yield 'string (S)' => [
            "\x00\x00\x00\x0e" . "\x03key" . "S\x00\x00\x00\x05value",
            ['key' => 'value'],
        ];

        yield 'array (A)' => [
            "\x00\x00\x00\x0e" . "\x03key" . "A\x00\x00\x00\x06b\x01b\x02b\x03",
            ['key' => [1, 2, 3]],
        ];

        yield 'timestamp - fits in PHP integer (T)' => [
            "\x00\x00\x00\x0d" . "\x03key" . "T\x7f\xff\xff\xff\xff\xff\xff\xff",
            ['key' => 9223372036854775807],
        ];

        yield 'timestamp (T)' => [
            "\x00\x00\x00\x0d" . "\x03key" . "T\xff\xff\xff\xff\xff\xff\xff\xff",
            ['key' => '18446744073709551615'],
        ];

        $nested = "\x00\x00\x00\x0e" . "\x03key" . "S\x00\x00\x00\x05value";

        yield 'nested table (F)' => [
            "\x00\x00\x00\x1A" . "\x06nested" . 'F' . $nested,
            ['nested' => ['key' => 'value']],
        ];

        yield 'void (V)' => [
            "\x00\x00\x00\x05" . "\x03key" . 'V',
            ['key' => null],
        ];

        yield 'byte-array (x)' => [
            "\x00\x00\x00\x0e" . "\x03key" . "x\x00\x00\x00\x05value",
            ['key' => 'value'],
        ];
    }

    /**
     * @dataProvider parseTestVectors
     */
    public function testParse($buffer, $expected)
    {
        $this->assertSame(
            $expected,
            $this->subject->parse($buffer)
        );

        $this->assertEquals(
            '',
            $buffer
        );
    }

    public function testParseDoesNotConsumeEntireBuffer()
    {
        $buffer = "\x00\x00\x00\x0e" . "\x03key" . "S\x00\x00\x00\x05value<remaining>";

        $this->subject->parse($buffer);

        $this->assertEquals(
            '<remaining>',
            $buffer
        );
    }

    public function testParseWithUnrecognisedType()
    {
        $buffer = "\x00\x00\x00\x0e" . "\x03key" . 'Z';

        $this->setExpectedException(
            ProtocolException::class,
            'The AMQP server has sent invalid data: table value type (0x5a) is invalid or unrecognised.'
        );

        $this->subject->parse($buffer);
    }
}
