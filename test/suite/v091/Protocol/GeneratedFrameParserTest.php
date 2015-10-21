<?php

namespace Recoil\Amqp\v091\Protocol;

use Eloquent\Phony\Phpunit\Phony;
use PHPUnit_Framework_TestCase;
use Recoil\Amqp\Exception\ProtocolException;
use Recoil\Amqp\v091\Protocol\Tx\TxCommitOkFrame;

class GeneratedFrameParserTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->tableParser = Phony::fullMock(TableParser::class);

        $this->subject = new GeneratedFrameParser(
            $this->tableParser->mock()
        );
    }

    public function testFeed()
    {
        // create binary data representing two frames spanning three separate
        // invocations to feed() ...
        $bufferA = chr(Constants::FRAME_HEARTBEAT)
                 . "\x00\x00" // channel
                 ;

        $bufferB = "\x00\x00\x00\x00" // size
                 . chr(Constants::FRAME_END)
                 . chr(Constants::FRAME_METHOD);

        $bufferC = "\x00\x01" // channel
                 . "\x00\x00\x00\x04" // size
                 . "\x00\x5a" // class (tx)
                 . "\x00\x15" // method (commit-ok)
                 . chr(Constants::FRAME_END);

        $this->assertEquals(
            array_merge(
                iterator_to_array($this->subject->feed($bufferA)),
                iterator_to_array($this->subject->feed($bufferB)),
                iterator_to_array($this->subject->feed($bufferC))
            ),
            [
                HeartbeatFrame::create(),
                TxCommitOkFrame::create(1),
            ]
        );
    }

    public function testFeedWithPayloadSpanningMultipleCalls()
    {
        // The first buffer contains enough data to satisfy the smallest possible
        // frame (ie, header + empty payload + 1 byte end marker), but not enough
        // for the *actual* frame.
        $bufferA = chr(Constants::FRAME_METHOD)
                 . "\x00\x01" // channel
                 . "\x00\x00\x00\x04" // size
                 . "\x00\x5a" // class (tx)
                 ;

        $bufferB = "\x00\x15" // method (commit-ok)
                 . chr(Constants::FRAME_END);

        $this->assertEquals(
            array_merge(
                iterator_to_array($this->subject->feed($bufferA)),
                iterator_to_array($this->subject->feed($bufferB))
            ),
            [
                TxCommitOkFrame::create(1),
            ]
        );
    }

    public function testFeedWithInvalidFrameType()
    {
        $buffer = chr(Constants::FRAME_HEARTBEAT + 1)
                . "\x00\x00" // channel
                . "\x00\x00\x00\x00" // size
                . chr(Constants::FRAME_END);

        $this->setExpectedException(
            ProtocolException::class,
            'The AMQP server has sent invalid data: Frame type (0x09) is invalid.'
        );

        iterator_to_array($this->subject->feed($buffer));
    }

    public function testFeedWithInvalidFrameEndMarker()
    {
        $buffer = chr(Constants::FRAME_HEARTBEAT)
                . "\x00\x00" // channel
                . "\x00\x00\x00\x00" // size
                . chr(Constants::FRAME_END + 1);

        $this->setExpectedException(
            ProtocolException::class,
            'The AMQP server has sent invalid data: Frame end marker (0xcf) is invalid.'
        );

        iterator_to_array($this->subject->feed($buffer));
    }

    public function testFeedWithNonZeroHeartbeatPayloadSize()
    {
        $buffer = chr(Constants::FRAME_HEARTBEAT)
                . "\x00\x00" // channel
                . "\x00\x00\x00\x01" // size
                . "\x00" // invalid payload
                . chr(Constants::FRAME_END);

        $this->setExpectedException(
            ProtocolException::class,
            'The AMQP server has sent invalid data: Heartbeat frame payload size (1) is invalid, must be zero.'
        );

        iterator_to_array($this->subject->feed($buffer));
    }

    public function testFeedWithIncorrectPayloadSize()
    {
        // This test checks an edge case that could occur if a frame is sent with
        // an incorrect payload size that just happens to point to an offset
        // where another valid frame end marker is found.
        $buffer = chr(Constants::FRAME_METHOD)
                . "\x00\x01" // channel
                . "\x00\x00\x00\x11" // invalid size, payload is ACTUALLY 8 bytes, not 17
                . "\x00\x0a" // class (connection)
                . "\x00\x14" // method (secure)
                . "\x00\x00\x00\x00" // channel field - long string reported as zero bytes
                . chr(Constants::FRAME_END)
                . chr(Constants::FRAME_HEARTBEAT)
                . "\x00\x00" // channel
                . "\x00\x00\x00\x00" // size
                . "\x00" // invalid payload
                . chr(Constants::FRAME_END);

        $this->setExpectedException(
            ProtocolException::class,
            'The AMQP server has sent invalid data: Mismatch between frame size (25) and consumed bytes (16).'
        );

        iterator_to_array($this->subject->feed($buffer));
    }
}
