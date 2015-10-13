<?php

namespace Recoil\Amqp\v091\React;

use Eloquent\Phony\Phpunit\Phony;
use function React\Promise\resolve;
use Icecave\Isolator\Isolator;
use PHPUnit_Framework_TestCase;
use React\EventLoop\LoopInterface;
use React\Socket\Connection as ReactStream;
use React\Stream\DuplexStreamInterface;
use Recoil\Amqp\ConnectionOptions;
use Recoil\Amqp\v091\Amqp091Connection;
use Recoil\Amqp\v091\Protocol\Connection\ConnectionStartFrame;
use Recoil\Amqp\v091\Protocol\Connection\ConnectionTuneFrame;
use Recoil\Amqp\v091\Protocol\FrameParser;
use Recoil\Amqp\v091\Protocol\FrameSerializer;
use Recoil\Amqp\v091\Protocol\Handshake;
use Recoil\Amqp\v091\Protocol\Transport;

class StreamConnectorTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        // Dependencies ...
        $this->loop = Phony::fullMock(LoopInterface::class);
        $this->parser = new FrameParser();
        $this->serializer = new FrameSerializer();
        $this->isolator = Phony::fullMock(Isolator::class);

        // Streams ...
        $this->isolator->stream_socket_client->returns('<fd>');
        $this->reactStream = Phony::fullMock(DuplexStreamInterface::class);
        $this->isolator->new->with(ReactStream::class, '*')->returns(
            $this->reactStream->mock()
        );

        // Handshake ...
        $this->startFrame = ConnectionStartFrame::create();
        $this->tuneFrame = ConnectionTuneFrame::create(0, 10, 20, 30);
        $this->handshake = Phony::fullMock(Handshake::class);
        $this->handshake->start->returns(
            resolve([
                $this->startFrame,
                $this->tuneFrame,
            ])
        );
        $this->isolator->new->with(StreamHandshake::class, '*')->returns(
            $this->handshake->mock()
        );

        // Transport ...
        $this->transport = Phony::fullMock(Transport::class);
        $this->isolator->new->with(StreamTransport::class, '*')->returns(
            $this->transport->mock()
        );

        // Per-connect objects ...
        $this->options = ConnectionOptions::create();

        $this->subject = new StreamConnector(
            $this->loop->mock()
        );

        $this->subject->setIsolator($this->isolator->mock());
    }

    public function testConnect()
    {
        $result = $this->subject->connect($this->options);

        $this->isolator->stream_socket_client->calledWith(
            'tcp://localhost:5672',
            null,
            null,
            STREAM_CLIENT_CONNECT | STREAM_CLIENT_ASYNC_CONNECT
        );

        $this->isolator->new->calledWith(
            ReactStream::class,
            '<fd>',
            $this->loop->mock()
        );

        $this->isolator->new->calledWith(
            StreamHandshake::class,
            $this->reactStream->mock(),
            $this->loop->mock(),
            $this->parser,
            $this->serializer
        );

        $this->isolator->new->calledWith(
            StreamHandshake::class,
            $this->reactStream->mock(),
            $this->loop->mock(),
            $this->parser,
            $this->serializer
        );

        $this->isolator->new->calledWith(
            StreamTransport::class,
            $this->reactStream->mock(),
            $this->loop->mock(),
            $this->parser,
            $this->serializer
        );

        Phony::inOrder(
            $this->handshake->start->calledWith($this->options),
            $this->transport->start->calledWith($this->options, $this->tuneFrame->heartbeat)
        );

        $then = Phony::stub();
        $result->then($then);

        $this->assertEquals(
            new Amqp091Connection(
                $this->transport->mock(),
                $this->tuneFrame->channelMax
            ),
            $then->called()->argument(0)
        );
    }

    public function testWithConnectFailure()
    {
        $this->markTestIncomplete();
    }

    public function testWithHandshakeFailure()
    {
        $this->markTestIncomplete();
    }
}
