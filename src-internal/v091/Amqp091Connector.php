<?php

namespace Recoil\Amqp\v091;

use Exception;
use function React\Promise\reject;
use Icecave\Isolator\IsolatorTrait;
use React\EventLoop\LoopInterface;
use React\Socket\Connection as SocketConnection;
use Recoil\Amqp\ConnectionOptions;
use Recoil\Amqp\Connector;
use Recoil\Amqp\Exception\ConnectionException;
use Recoil\Amqp\v091\Transport\ConnectionController;
use Recoil\Amqp\v091\Transport\HandshakeController;
use Recoil\Amqp\v091\Transport\StreamTransport;
use RuntimeException;

/**
 * Establishes a connection to an AMQP server.
 */
final class Amqp091Connector implements Connector
{
    /**
     * @param LoopInterface $loop The event loop that services connections created by this connector.
     */
    public function __construct(LoopInterface $loop)
    {
        $this->loop = $loop;
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
        $iso = $this->isolator();
        $start = $iso->microtime(true);

        try {
            $fd = $this->openConnection($options);
        } catch (Exception $e) {
            return reject($e);
        }

        // Create a React stream from the raw PHP stream ...
        $stream = $iso->new(
            SocketConnection::class,
            $fd,
            $this->loop
        );

        // Create the AMQP transport from the stream ...
        $transport = $iso->new(
            StreamTransport::class,
            $stream
        );

        // Compute the remaining timeout window that's availble to complete the
        // handshake ...
        $elapsed = $iso->microtime(true) - $start;

        // Create a controller that manages the handshake ...
        $handshakeController = $iso->new(
            HandshakeController::class,
            $this->loop,
            $options,
            $options->timeout() - $elapsed
        );

        // Start the AMQP handshake ...
        return $handshakeController->start($transport)
            // Handshake successful, switch to the established connection
            // controller ...
            ->then(
                function ($handshakeResult) use ($iso, $transport, $options) {
                    $connectionController = $iso->new(
                        ConnectionController::class,
                        $this->loop,
                        $options,
                        $handshakeResult
                    );

                    return $connectionController->start($transport);
                }
            )
            // Connection controllers started successfully, give the caller
            // their connection ...
            ->then(
                function ($serverApi) use ($iso) {
                    return $iso->new(
                        Amqp091Connection::class,
                        $serverApi
                    );
                }
            );
    }

    /**
     * @param ConnectionOptions $options
     *
     * @return tuple<resource, float> A 2-tuple containing the stream resource and the time taken to connect, in seconds.
     * @throws ConnectionException
     */
    private function openConnection(ConnectionOptions $options)
    {
        $iso = $this->isolator();

        $errorCode = null;
        $errorMessage = null;

        $fd = @$iso->stream_socket_client(
            sprintf(
                'tcp://%s:%s',
                $options->host(),
                $options->port()
            ),
            $errorCode,
            $errorMessage,
            $options->timeout(),

            // @todo Connect asynchronously.
            // @link https://github.com/recoilphp/amqp/issues/22
            STREAM_CLIENT_CONNECT // | STREAM_CLIENT_ASYNC_CONNECT
        );

        if (false === $fd) {
            throw ConnectionException::couldNotConnect(
                $options,
                new RuntimeException(
                    $errorMessage,
                    $errorCode
                )
            );
        } elseif (false === @$iso->stream_set_blocking($fd, false)) {
            throw ConnectionException::couldNotConnect(
                $options,
                new RuntimeException('Unable to set socket to non-blocking.')
            );
        }

        return $fd;
    }

    use IsolatorTrait;

    /**
     * @var LoopInterface The event loop that services connections created by this connector.
     */
    private $loop;
}
