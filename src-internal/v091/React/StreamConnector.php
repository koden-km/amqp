<?php
namespace Recoil\Amqp\v091\React;

use Icecave\Isolator\IsolatorTrait;
use React\EventLoop\LoopInterface;
use React\Socket\Connection as SocketStream;
use Recoil\Amqp\Connection;
use Recoil\Amqp\ConnectionException;
use Recoil\Amqp\ConnectionOptions;
use Recoil\Amqp\Connector;
use Recoil\Amqp\v091\Amqp091Connection;
use RuntimeException;
use function React\Promise\reject;

/**
 * A connection to an AMQP server.
 */
final class StreamConnector implements Connector
{
    public function __construct(
        ConnectionOptions $options,
        LoopInterface $loop
    ) {
        $this->options = $options;
        $this->loop = $loop;
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

        $transport = new StreamTransport(
            new SocketStream($stream, $this->loop),
            $this->loop
        );

        return $transport
            ->handshake($this->options)
            ->then(function () use ($transport) {
                return new Amqp091Connection($transport);
            });
    }

    use IsolatorTrait;

    private $options;
    private $loop;
}
