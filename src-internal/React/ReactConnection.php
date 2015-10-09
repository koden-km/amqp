<?php
namespace Recoil\Amqp\React;

use Icecave\Isolator\IsolatorTrait;
use React\EventLoop\LoopInterface;
use React\Socket\Connection as SocketConnection;
use Recoil\Amqp\Connection;
use Recoil\Amqp\ConnectionOptions;
use Recoil\Amqp\PackageInfo;
use Recoil\Amqp\Protocol\v091\Amqp091FrameParser;
use Recoil\Amqp\Protocol\v091\Amqp091FrameSerializer;
use Recoil\Amqp\Protocol\v091\Connection\CloseFrame;
use Recoil\Amqp\Protocol\v091\Connection\CloseOkFrame;
use Recoil\Amqp\Protocol\v091\Connection\OpenFrame;
use Recoil\Amqp\Protocol\v091\Connection\StartFrame;
use Recoil\Amqp\Protocol\v091\Connection\StartOkFrame;
use Recoil\Amqp\Protocol\v091\Connection\TuneFrame;
use Recoil\Amqp\Protocol\v091\Connection\TuneOkFrame;

/**
 * A connection to an AMQP server that uses React's event loop.
 */
final class ReactConnection implements Connection
{
    public function __construct(
        LoopInterface $loop,
        ConnectionOptions $options,
        FrameParser $parser = null,
        FrameSerializer $serializer = null
    ) {
        $this->loop = $loop;
        $this->options = $options;
        $this->parser = $parser ?: new Amqp091FrameParser();
        $this->serializer = $serializer ?: new Amqp091FrameSerializer();
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
        foreach ($this->parser->feed($buffer) as $frame) {
            print_r($frame);

            if ($frame instanceof StartFrame) {
                $response = new StartOkFrame();
                $response->channel = 0;
                $response->clientProperties = [
                    'product'  => PackageInfo::NAME,
                    'version'  => PackageInfo::VERSION,
                    'platform' => 'PHP ' . phpversion(),
                ];
                $response->mechanism = 'AMQPLAIN';
                $response->response = $this->serializer->serializePlainCredentials(
                    $this->options->username(),
                    $this->options->password()
                );
                $response->locale = 'en_US';

                $this->send($response);
            } elseif ($frame instanceof TuneFrame) {
                $response = new TuneOkFrame();
                $response->channel = 0;
                $response->channelMax = $frame->channelMax;
                $response->frameMax = $frame->frameMax;
                $response->heartbeat = $frame->heartbeat;

                $this->send($response);

                $response = new OpenFrame();
                $response->channel = 0;
                $response->virtualHost = $this->options->vhost();
                $response->capabilities = '';
                $response->insist = false;

                $this->send($response);
            } elseif ($frame instanceof CloseFrame) {
                $response = new CloseOkFrame();
                $response->channel = 0;

                $this->send($response);
            }
        }
    }

    private function send($frame)
    {
        print_r($frame);

        $this->stream->write(
            $this->serializer->serialize($frame)
        );
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
    private $parser;
    private $serializer;
}
