<?php

namespace Recoil\Amqp\v091;

use Eloquent\Phony\Phpunit\Phony;
use function React\Promise\resolve;
use Icecave\Isolator\Isolator;
use PHPUnit_Framework_TestCase;
use React\EventLoop\LoopInterface;
use React\Stream\DuplexStreamInterface;
use React\Stream\Stream;
use Recoil\Amqp\ConnectionOptions;
use Recoil\Amqp\PromiseTestTrait;
use Recoil\Amqp\v091\Transport\ConnectionController;
use Recoil\Amqp\v091\Transport\HandshakeController;
use Recoil\Amqp\v091\Transport\HandshakeResult;
use Recoil\Amqp\v091\Transport\ServerApi;
use Recoil\Amqp\v091\Transport\StreamTransport;
use Recoil\Amqp\v091\Transport\Transport;
use Recoil\Amqp\v091\Transport\TransportController;

class Amqp091ConnectorTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->loop = Phony::fullMock(LoopInterface::class);
        $this->isolator = Phony::fullMock(Isolator::class);
        $this->options = ConnectionOptions::create();

        // Streams ...
        $this->isolator->stream_socket_client->returns('<fd>');
        $this->isolator->stream_set_blocking->returns(true);
        $this->stream = Phony::fullMock(DuplexStreamInterface::class);

        $this->isolator->new->with(Stream::class, '*')->returns(
            $this->stream->mock()
        );

        // Transport and controllers ...
        $this->transport = Phony::fullMock(Transport::class);
        $this->handshakeController = Phony::fullMock(TransportController::class);
        $this->connectionController = Phony::fullMock(TransportController::class);

        $this->isolator->new->with(StreamTransport::class, '*')->returns(
            $this->transport->mock()
        );
        $this->isolator->new->with(HandshakeController::class, '*')->returns(
            $this->handshakeController->mock()
        );
        $this->isolator->new->with(ConnectionController::class, '*')->returns(
            $this->connectionController->mock()
        );

        $this->handshakeResult = new HandshakeResult();
        $this->handshakeController->start->returns(
            resolve($this->handshakeResult)
        );

        $this->serverApi = Phony::fullMock(ServerApi::class);
        $this->connectionController->start->returns(
            resolve($this->serverApi->mock())
        );

        // Test subject ...
        $this->subject = new Amqp091Connector(
            $this->loop->mock()
        );

        $this->subject->setIsolator($this->isolator->mock());
    }

    public function testConnect()
    {
        $promise = $this->subject->connect($this->options);

        $this->assertEquals(
            new Amqp091Connection(
                $this->serverApi->mock()
            ),
            $this->assertResolved($promise)
        );
    }

    use PromiseTestTrait;
}
