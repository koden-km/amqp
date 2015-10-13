<?php

namespace Recoil\Amqp\v091\React;

use function React\Promise\reject;
use Icecave\Isolator\IsolatorTrait;
use React\EventLoop\LoopInterface;
use React\Socket\Connection as ReactStream;
use Recoil\Amqp\Connection;
use Recoil\Amqp\ConnectionOptions;
use Recoil\Amqp\Connector;
use Recoil\Amqp\Exception\ConnectionException;
use Recoil\Amqp\v091\Amqp091Connection;
use Recoil\Amqp\v091\Protocol\FrameParser;
use Recoil\Amqp\v091\Protocol\FrameSerializer;
use RuntimeException;

/**
 * A connection to an AMQP server.
 */
final class StreamConnector implements Connector
{
    public function __construct(
        LoopInterface $loop,
        FrameParser $parser = null,
        FrameSerializer $serializer = null
    ) {
        $this->loop = $loop;
        $this->parser = $parser ?: new FrameParser();
        $this->serializer = $serializer ?: new FrameSerializer();
    }

    /**
     * Connect to an AMQP server.
     *
     * @param ConnectionOptions $options The options used when establishing the connection.
     *
     * Via promise:
     * @return Connection          The AMQP connection.
     * @throws ConnectionException if the connection could not be established.
     */
    public function connect(ConnectionOptions $options)
    {
        $errorCode = null;
        $errorMessage = null;

        $iso = $this->isolator();

        $socket = @$iso->stream_socket_client(
            sprintf(
                'tcp://%s:%s',
                $options->host(),
                $options->port()
            ),
            $errorCode,
            $errorMessage,
            5, // connection timeout - TODO: pull from connection options
            STREAM_CLIENT_CONNECT | STREAM_CLIENT_ASYNC_CONNECT
        );

        if (false === $socket) {
            return reject(
                ConnectionException::couldNotConnect(
                    $options,
                    new RuntimeException(
                        $errorMessage,
                        $errorCode
                    )
                )
            );
        }

        $iso->stream_set_blocking($socket, false);

        $stream = $iso->new(
            ReactStream::class,
            $socket,
            $this->loop
        );

        $handshake = $iso->new(
            StreamHandshake::class,
            $stream,
            $this->loop,
            $this->parser,
            $this->serializer
        );

        return $handshake
            ->start($options)
            ->then(function ($frames) use ($iso, $stream, $options) {
                list($startFrame, $tuneFrame) = $frames;

                $transport = $iso->new(
                    StreamTransport::class,
                    $stream,
                    $this->loop,
                    $this->parser,
                    $this->serializer
                );

                $transport->start($options, $tuneFrame->heartbeat);

                return new Amqp091Connection(
                    $transport,
                    $tuneFrame->channelMax
                );
            });
    }

    use IsolatorTrait;

    private $loop;
    private $parser;
    private $serializer;
}
