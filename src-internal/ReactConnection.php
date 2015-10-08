<?php
namespace Recoil\Amqp;

use Icecave\Isolator\IsolatorTrait;
use React\EventLoop\LoopInterface;
use React\Socket\Connection as SocketConnection;
use Recoil\Amqp\Protocol\Connection\CloseFrame;
use Recoil\Amqp\Protocol\Connection\CloseOkFrame;
use Recoil\Amqp\Protocol\Connection\OpenFrame;
use Recoil\Amqp\Protocol\Connection\StartFrame;
use Recoil\Amqp\Protocol\Connection\StartOkFrame;
use Recoil\Amqp\Protocol\Connection\TuneFrame;
use Recoil\Amqp\Protocol\Connection\TuneOkFrame;
use Recoil\Amqp\Protocol\FrameParser;
use Recoil\Amqp\Protocol\FrameSerializer;

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
        $this->parser = new FrameParser();
        $this->serializer = new FrameSerializer();
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
        // echo self::hex($buffer);

        $this->parser->append($buffer);

        while ($frame = $this->parser->parseFrame()) {
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
                $response->response = $this->serializer->serializeCredentials(
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

    private static function hex($buffer, $width = 32)
    {
        static $from = '';
        static $to = '';
        static $pad = '.'; # padding for non-visible characters

        if ($from === '') {
            for ($i = 0; $i <= 0xff; ++$i) {
                $from .= chr($i);
                $to .= ($i >= 0x20 && $i <= 0x7e) ? chr($i) : $pad;
            }
        }

        $hex = str_split(
            bin2hex($buffer),
            $width * 2
        );

        $chars = str_split(
            strtr($buffer, $from, $to),
            $width
        );

        $offset = 0;
        $output = '';

        foreach ($hex as $i => $line) {
            $output .= sprintf(
                '%6d : %-' . ($width * 3 - 1) . 's [%s]' . PHP_EOL,
                $offset,
                implode(' ', str_split($line, 2)),
                $chars[$i]
            );

            $offset += $width;
        }

        return $output;
    }

    use IsolatorTrait;

    private $loop;
    private $options;
    private $stream;
    private $parser;
    private $serializer;
}
