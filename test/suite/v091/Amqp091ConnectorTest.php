<?php

namespace Recoil\Amqp\v091;

use Eloquent\Phony\Phpunit\Phony;
use Exception;
use function React\Promise\reject;
use function React\Promise\resolve;
use Icecave\Isolator\Isolator;
use PHPUnit_Framework_TestCase;
use React\EventLoop\LoopInterface;
use React\Stream\DuplexStreamInterface;
use React\Stream\Stream;
use Recoil\Amqp\ConnectionOptions;
use Recoil\Amqp\Exception\ConnectionException;
use Recoil\Amqp\PromiseTestTrait;
use Recoil\Amqp\v091\Transport\ConnectionController;
use Recoil\Amqp\v091\Transport\HandshakeController;
use Recoil\Amqp\v091\Transport\HandshakeResult;
use Recoil\Amqp\v091\Transport\ServerApi;
use Recoil\Amqp\v091\Transport\StreamTransport;
use Recoil\Amqp\v091\Transport\Transport;
use Recoil\Amqp\v091\Transport\TransportController;
use RuntimeException;

class Amqp091ConnectorTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->loop = Phony::mock(LoopInterface::class);
        $this->isolator = Phony::mock(Isolator::class);
        $this->options = ConnectionOptions::create();

        // Elapsed timer ...
        $this->isolator->microtime->returns(0.5, 2.0);

        // Streams ...
        $this->isolator->stream_socket_client->returns('<fd>');
        $this->isolator->stream_set_blocking->returns(true);
        $this->stream = Phony::mock(DuplexStreamInterface::class);

        $this->isolator->new->with(Stream::class, '*')->returns(
            $this->stream->mock()
        );

        // Transport and controllers ...
        $this->transport = Phony::mock(Transport::class);
        $this->handshakeController = Phony::mock(TransportController::class);
        $this->connectionController = Phony::mock(TransportController::class);

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

        $this->serverApi = Phony::mock(ServerApi::class);
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

        Phony::inOrder(
            $this->isolator->stream_socket_client->calledWith(
                'tcp://localhost:5672',
                null,
                null,
                $this->options->timeout(),
                STREAM_CLIENT_CONNECT // | STREAM_CLIENT_ASYNC_CONNECT
            ),
            $this->isolator->stream_set_blocking->calledWith(
                '<fd>',
                false
            ),
            $this->isolator->new->calledWith(
                StreamTransport::class,
                $this->stream->mock()
            ),
            $this->isolator->new->calledWith(
                HandshakeController::class,
                $this->loop->mock(),
                $this->options,
                $this->options->timeout() - 1.5 // 1.5 diff between microtime() calls
            ),
            $this->handshakeController->start->calledWith(
                $this->transport->mock()
            ),
            $this->isolator->new->calledWith(
                ConnectionController::class,
                $this->loop->mock(),
                $this->options,
                $this->handshakeResult
            ),
            $this->connectionController->start->calledWith(
                $this->transport->mock()
            )
        );

        $this->assertEquals(
            new Amqp091Connection(
                $this->serverApi->mock()
            ),
            $this->assertResolved($promise)
        );
    }

    public function testSocketCreationFailure()
    {
        $this->isolator->stream_socket_client
            ->setsArgument(1, 54321)
            ->setsArgument(2, '<message>')
            ->returns(false);

        $promise = $this->subject->connect($this->options);

        $this->isolator->stream_set_blocking->never()->called();
        $this->transport->noInteraction();
        $this->connectionController->noInteraction();
        $this->handshakeController->noInteraction();

        $this->assertEquals(
            ConnectionException::couldNotConnect(
                $this->options,
                '54321: <message>'
            ),
            $this->assertRejected($promise)
        );
    }

    public function testSocketSetNonBlockingFailure()
    {
        $this->isolator->stream_set_blocking->returns(false);

        $promise = $this->subject->connect($this->options);

        $this->transport->noInteraction();
        $this->connectionController->noInteraction();
        $this->handshakeController->noInteraction();

        $this->assertEquals(
            new RuntimeException('Unable to set socket to non-blocking.'),
            $this->assertRejected($promise)
        );
    }

    public function testHandshakeControllerStartFailure()
    {
        $exception = new Exception('The exception!');

        $this->handshakeController->start->returns(
            reject($exception)
        );

        $promise = $this->subject->connect($this->options);

        $this->connectionController->noInteraction();

        $this->assertSame(
            $exception,
            $this->assertRejected($promise)
        );
    }

    public function testConnectionControllerStartFailure()
    {
        $exception = new Exception('The exception!');

        $this->connectionController->start->returns(
            reject($exception)
        );

        $promise = $this->subject->connect($this->options);

        $this->assertSame(
            $exception,
            $this->assertRejected($promise)
        );
    }

    use PromiseTestTrait;
}
