<?php
namespace Recoil\Amqp;

use Icecave\Isolator\IsolatorTrait;
use React\EventLoop\LoopInterface;
use React\Socket\Connection as SocketConnection;
use Recoil\Amqp\Protocol\FrameReader;

/**
 * A connection to an AMQP server that uses React's event loop.
 */
final class ReactConnection implements Connection
{
    public function __construct(
        LoopInterface $loop,
        ConnectionOptions $options
    ) {
        $this->loop = $loop;
        $this->options = $options;
        $this->reader = new FrameReader();
    }

    /**
     * Connect to the server.
     */
    public function connect()
    {
        if ($this->stream) {
            throw new LogicException(
                'Already connected.'
            );
        }

        $iso = $this->isolator();
        $errorNumber = null;
        $errorString = null;

        $stream = $iso->stream_socket_client(
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
            throw new RuntimeException(
                $errorString,
                $errorNumber
            );
        }

        $iso->stream_set_blocking($stream, false);

        $this->stream = $iso->new(
            SocketConnection::class,
            $stream,
            $this->loop
        );

        $this->stream->on(
            'data',
            function ($buffer) {
                $this->onData($buffer);
            }
        );

        $this->stream->on(
            'close',
            function () {
                $this->onClose();
            }
        );

        $this->stream->on(
            'drain',
            function () {
                $this->onDrain();
            }
        );

        $this->stream->on(
            'error',
            function ($error) {
                $this->onError($error);
            }
        );

        $this->stream->write("AMQP\x00\x00\x09\x01");
    }

    /**
     * Create a new AMQP channel.
     *
     * Via promise:
     * @return Channel             The newly created channel.
     * @throws ConnectionException if not connected to the AMQP server.
     */
    public function channel()
    {
    }

    /**
     * Disconnect from the server.
     *
     * Via promise:
     * @return null
     */
    public function close()
    {
    }

    private function onData($buffer)
    {
        $this->reader->append($buffer);

        while ($frame = $this->reader->readFrame()) {
            print_r($frame);
        }
    }

    private function onDrain()
    {
        var_dump(__METHOD__, func_get_args());
    }

    private function onClose()
    {
        var_dump(__METHOD__, func_get_args());
    }

    private function onError($error)
    {
        var_dump(__METHOD__, func_get_args());
    }

    use IsolatorTrait;

    private $loop;
    private $options;
    private $stream;
    private $reader;
}
