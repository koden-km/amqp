<?php

namespace Recoil\Amqp\v091\React;

use Eloquent\Phony\Phpunit\Phony;
use function React\Promise\reject;
use function React\Promise\resolve;
use Icecave\Isolator\Isolator;
use PHPUnit_Framework_TestCase;
use React\EventLoop\LoopInterface;
use React\Socket\Connection as ReactStream;
use React\Stream\DuplexStreamInterface;
use Recoil\Amqp\ConnectionOptions;
use Recoil\Amqp\Exception\ConnectionException;
use Recoil\Amqp\v091\Amqp091Connection;
use Recoil\Amqp\v091\Protocol\Connection\ConnectionStartFrame;
use Recoil\Amqp\v091\Protocol\Connection\ConnectionTuneFrame;
use Recoil\Amqp\v091\Protocol\FrameParser;
use Recoil\Amqp\v091\Protocol\FrameSerializer;
use Recoil\Amqp\v091\Protocol\Handshake;
use Recoil\Amqp\v091\Protocol\Transport;
use RuntimeException;

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
        $this->isolator->stream_socket_client->returns('<socket>');
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
        $promise = $this->subject->connect($this->options);

        $this->isolator->stream_socket_client->calledWith(
            'tcp://localhost:5672',
            null,
            null,
            5, // timeout - TODO pull from connection options
            STREAM_CLIENT_CONNECT | STREAM_CLIENT_ASYNC_CONNECT
        );

        $this->isolator->stream_set_blocking->calledWith('<socket>', false);

        $this->isolator->new->calledWith(
            ReactStream::class,
            '<socket>',
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

        $stub = Phony::stub();
        $promise->then($stub);

        $this->assertEquals(
            new Amqp091Connection(
                $this->transport->mock(),
                $this->tuneFrame->channelMax
            ),
            $stub->called()->argument(0)
        );
    }

    public function testConnectWithSocketFailure()
    {
        $this->isolator->stream_socket_client
            ->setsArgument(1, 123)
            ->setsArgument(2, '<message>')
            ->returns(false);

        $promise = $this->subject->connect($this->options);

        $this->isolator->new->never()->called();

        $stub = Phony::stub();
        $promise->otherwise($stub);

        $this->assertEquals(
            ConnectionException::couldNotConnect(
                $this->options,
                new RuntimeException('<message>', 123)
            ),
            $stub->called()->argument(0)
        );
    }

    public function testConnectWithHandshakeFailure()
    {
        $exception = new RuntimeException('Handshake failure!');

        $this->handshake->start->returns(
            reject($exception)
        );

        $stub = Phony::stub();
        $this->subject->connect($this->options)->otherwise($stub);

        $this->assertSame(
            $exception,
            $stub->called()->argument(0)
        );
    }
}
