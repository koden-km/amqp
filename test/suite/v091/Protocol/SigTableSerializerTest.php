<?php

namespace Recoil\Amqp\v091\Protocol;

use PHPUnit_Framework_TestCase;
use Recoil\Amqp\Exception\ProtocolException;

class SigTableSerializerTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->subject = new SigTableSerializer();
    }

    public function serializeTestVectors()
    {
        yield 'empty' => [
            [],
            "\x00\x00\x00\x00",
        ];

        yield 'boolean (t)' => [
            [
                'true' => true,
                'false' => false,
            ],
            "\x00\x00\x00\x0f"
            . "\x04true" . "t\x01"
            . "\x05false" . "t\x00",
        ];

        // yield 'signed octet - pos (b)' => [
        //     ['key' => 127],
        //     "\x00\x00\x00\x06" . "\x03key" . "b\x7f",
        // ];

        // yield 'signed octet - neg (b)' => [
        //     ['key' => -2],
        //     "\x00\x00\x00\x06" . "\x03key" . "b\xfe",
        // ];

        // yield 'signed short - pos (s)' => [
        //     ['key' => 32767],
        //     "\x00\x00\x00\x07" . "\x03key" . "s\x7f\xff",
        // ];

        // yield 'signed short - neg (s)' => [
        //     ['key' => -2],
        //     "\x00\x00\x00\x07" . "\x03key" . "s\xff\xfe",
        // ];

        // yield 'signed long - pos (I)' => [
        //     ['key' => 2147483647],
        //     "\x00\x00\x00\x09" . "\x03key" . "I\x7f\xff\xff\xff",
        // ];

        // yield 'signed long - neg (I)' => [
        //     ['key' => -2],
        //     "\x00\x00\x00\x09" . "\x03key" . "I\xff\xff\xff\xfe",
        // ];

        yield 'signed long long - pos (l)' => [
            ['key' => 9223372036854775807],
            "\x00\x00\x00\x0d" . "\x03key" . "l\x7f\xff\xff\xff\xff\xff\xff\xff",
        ];

        yield 'signed long long - neg (l)' => [
            ['key' => -9223372036854775807 - 1], // use operator to work around PHP parse bug
            "\x00\x00\x00\x0d" . "\x03key" . "l\x80\x00\x00\x00\x00\x00\x00\x00",
        ];

        // yield 'float (f)' => [
        //     ['key' => 3.1414999961853027],
        //     "\x00\x00\x00\x09" . "\x03key" . "f\x40\x49\x0e\x56",
        // ];

        yield 'double (d)' => [
            ['key' => 3.14150000000000018118839761883],
            "\x00\x00\x00\x0d" . "\x03key" . "d\x40\x09\x21\xCA\xC0\x83\x12\x6F",
        ];

        // yield 'decimal - pos (D)' => [
        //     ['key' => '3.14'],
        //     "\x00\x00\x00\x0a" . "\x03key" . "D\x02\x00\x00\x01\x3a",
        // ];

        // yield 'decimal - pos - zero scale (D)' => [
        //     ['key' => '314'],
        //     "\x00\x00\x00\x0a" . "\x03key" . "D\x00\x00\x00\x01\x3a",
        // ];

        // yield 'decimal - pos - scale same as string length (D)' => [
        //     ['key' => '0.314'],
        //     "\x00\x00\x00\x0a" . "\x03key" . "D\x03\x00\x00\x01\x3a",
        // ];

        // yield 'decimal - pos - scale larger than string length (D)' => [
        //     ['key' => '0.00314'],
        //     "\x00\x00\x00\x0a" . "\x03key" . "D\x05\x00\x00\x01\x3a",
        // ];

        // yield 'decimal - neg (D)' => [
        //     ['key' => '-3.14'],
        //     "\x00\x00\x00\x0a" . "\x03key" . "D\x02\xff\xff\xfe\xc6",
        // ];

        // yield 'decimal - neg - zero scale (D)' => [
        //     ['key' => '-314'],
        //     "\x00\x00\x00\x0a" . "\x03key" . "D\x00\xff\xff\xfe\xc6",
        // ];

        // yield 'decimal - neg - scale same as string length (D)' => [
        //     ['key' => '-0.314'],
        //     "\x00\x00\x00\x0a" . "\x03key" . "D\x03\xff\xff\xfe\xc6",
        // ];

        // yield 'decimal - neg - scale larger than string length (D)' => [
        //     ['key' => '-0.00314'],
        //     "\x00\x00\x00\x0a" . "\x03key" . "D\x05\xff\xff\xfe\xc6",
        // ];

        yield 'string (S)' => [
            ['key' => 'value'],
            "\x00\x00\x00\x0e" . "\x03key" . "S\x00\x00\x00\x05value",
        ];

        yield 'array (A)' => [
            ['key' => [1, 2, 3]],
            "\x00\x00\x00\x0f" . "\x03key" . "A\x00\x00\x00\x06b\x01b\x02b\x03",
        ];

        // yield 'timestamp - fits in PHP integer (T)' => [
        //     ['key' => 9223372036854775807],
        //     "\x00\x00\x00\x0d" . "\x03key" . "T\x7f\xff\xff\xff\xff\xff\xff\xff",
        // ];

        // yield 'timestamp (T)' => [
        //     ['key' => '18446744073709551615'],
        //     "\x00\x00\x00\x0d" . "\x03key" . "T\xff\xff\xff\xff\xff\xff\xff\xff",
        // ];

        $nested = "\x00\x00\x00\x0e" . "\x03key" . "S\x00\x00\x00\x05value";

        yield 'nested table (F)' => [
            ['nested' => ['key' => 'value']],
            "\x00\x00\x00\x1a" . "\x06nested" . 'F' . $nested,
        ];

        yield 'void (V)' => [
            ['key' => null],
            "\x00\x00\x00\x05" . "\x03key" . 'V',
        ];

        // yield 'byte-array (x)' => [
        //     ['key' => 'value'],
        //     "\x00\x00\x00\x0e" . "\x03key" . "x\x00\x00\x00\x05value",
        // ];
    }

    /**
     * @dataProvider serializeTestVectors
     */
    public function testSerialize($table, $expected)
    {
        $this->assertSame(
            chunk_split(bin2hex($expected), 2, ' '),
            chunk_split(bin2hex($this->subject->serialize($table)), 2, ' ')
        );
    }
}
