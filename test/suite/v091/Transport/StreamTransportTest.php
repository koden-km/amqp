<?php

namespace Recoil\Amqp\v091\Transport;

use Eloquent\Phony\Phpunit\Phony;
use PHPUnit_Framework_TestCase;
use React\Stream\DuplexStreamInterface;
use Recoil\Amqp\v091\Protocol\FrameParser;
use Recoil\Amqp\v091\Protocol\FrameSerializer;

class StreamTransportTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->stream = Phony::fullMock(DuplexStreamInterface::class);
        $this->parser = Phony::fullMock(FrameParser::class);
        $this->serializer = Phony::fullMock(FrameSerializer::class);

        $this->subject = new StreamTransport(
            $this->stream->mock(),
            $this->parser->mock(),
            $this->serializer->mock()
        );
    }

    public function testConstructorPausesStream()
    {
        $this->stream->pause->called();
    }
}
