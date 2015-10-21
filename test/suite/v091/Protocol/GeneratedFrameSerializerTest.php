<?php

namespace Recoil\Amqp\v091\Protocol;

use Eloquent\Phony\Phpunit\Phony;
use PHPUnit_Framework_TestCase;
use Recoil\Amqp\v091\Protocol\Connection\ConnectionTuneOkFrame;

class GeneratedFrameSerializerTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->tableSerializer = Phony::fullMock(TableSerializer::class);

        $this->subject = new GeneratedFrameSerializer(
            $this->tableSerializer->mock()
        );
    }

    public function testSerializeMethodFrame()
    {
        $expected = chr(Constants::FRAME_METHOD)
                  . "\x00\x01" // channel
                  . "\x00\x00\x00\x0c" // size
                  . "\x00\x0a" // class
                  . "\x00\x1f" // method
                  . "\x00\x02" // channel max
                  . "\x00\x00\x00\x03" // frame max
                  . "\x00\x04" // heartbeat
                  . chr(Constants::FRAME_END);

        $this->assertSame(
            chunk_split(bin2hex($expected), 2, ' '),
            chunk_split(
                bin2hex(
                    $this->subject->serialize(
                        ConnectionTuneOkFrame::create(1, 2, 3, 4)
                    )
                ),
                2,
                ' '
            )
        );
    }

    public function testSerializerContentHeaderFrame()
    {
        $this->markTestIncomplete();
    }

    public function testSerializerContentBodyFrame()
    {
        $this->markTestIncomplete();
    }

    public function testSerializeHeartbeatFrame()
    {
        $expected = chr(Constants::FRAME_HEARTBEAT)
                  . "\x00\x00" // channel
                  . "\x00\x00\x00\x00" // size
                  . chr(Constants::FRAME_END);

        $this->assertSame(
            $expected,
            $this->subject->serialize(
                HeartbeatFrame::create()
            )
        );
    }
}
