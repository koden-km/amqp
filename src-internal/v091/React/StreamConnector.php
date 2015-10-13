<?php

namespace Recoil\Amqp\v091\React;

use function React\Promise\reject;
use Icecave\Isolator\IsolatorTrait;
use React\EventLoop\LoopInterface;
use React\Socket\Connection as SocketStream;
use Recoil\Amqp\Connection;
use Recoil\Amqp\ConnectionException;
use Recoil\Amqp\ConnectionOptions;
use Recoil\Amqp\Connector;
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
        ConnectionOptions $options,
        LoopInterface $loop,
        FrameParser $parser = null,
        FrameSerializer $serializer = null
    ) {
        $this->options = $options;
        $this->loop = $loop;
        $this->parser = $parser ?: new FrameParser();
        $this->serializer = $serializer ?: new FrameSerializer();
    }

    /**
     * Connect to an AMQP server.
     *
     * Via promise:
     * @return Connection          The AMQP connection.
     * @throws ConnectionException if the connection could not be established.
     */
    public function connect()
    {
        $errorNumber = null;
        $errorString = null;

        $stream = @$this->isolator()->stream_socket_client(
            sprintf(
                'tcp://%s:%s',
                $this->options->host(),
                $this->options->port()
            ),
            $errorNumber,
            $errorString,
            STREAM_CLIENT_CONNECT | STREAM_CLIENT_ASYNC_CONNECT
        );

        if (false === $stream) {
            return reject(
                ConnectionException::couldNotConnect(
                    $this->options,
                    new RuntimeException(
                        $errorString,
                        $errorNumber
                    )
                )
            );
        }

        $this->isolator()->stream_set_blocking($stream, false);

        $stream = new SocketStream(
            $stream,
            $this->loop
        );

        $handshake = new StreamHandshake(
            $stream,
            $this->options,
            $this->loop,
            $this->parser,
            $this->serializer
        );

        return $handshake
            ->start()
            ->then(function ($frames) use ($stream) {
                list($startFrame, $tuneFrame) = $frames;

                $transport = new StreamTransport(
                    $stream,
                    $this->options,
                    $this->loop,
                    $this->parser,
                    $this->serializer
                );

                $transport->start($tuneFrame->heartbeat);

                return new Amqp091Connection(
                    $transport,
                    $tuneFrame->channelMax
                );
            });
    }

    use IsolatorTrait;

    private $options;
    private $loop;
    private $parser;
    private $serializer;
}
