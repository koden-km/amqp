<?php
namespace Recoil\Amqp\v091\React;

use Exception;
use LogicException;
use React\EventLoop\LoopInterface;
use React\Promise\Deferred;
use React\Stream\DuplexStreamInterface;
use Recoil\Amqp\ConnectionException;
use Recoil\Amqp\ConnectionOptions;
use Recoil\Amqp\PackageInfo;
use Recoil\Amqp\ProtocolException;
use Recoil\Amqp\v091\Protocol\Connection\ConnectionOpenFrame;
use Recoil\Amqp\v091\Protocol\Connection\ConnectionOpenOkFrame;
use Recoil\Amqp\v091\Protocol\Connection\ConnectionStartFrame;
use Recoil\Amqp\v091\Protocol\Connection\ConnectionStartOkFrame;
use Recoil\Amqp\v091\Protocol\Connection\ConnectionTuneFrame;
use Recoil\Amqp\v091\Protocol\Connection\ConnectionTuneOkFrame;
use Recoil\Amqp\v091\Protocol\FrameParser;
use Recoil\Amqp\v091\Protocol\FrameSerializer;

/**
 * Prepares a stream for use by StreamTransport by performing the AMQP handshake.
 */
final class StreamHandshake
{
    /**
     * @param DuplexStreamInterface $stream     The stream to use for communication, typically a TCP connection.
     * @param ConnectionOptions     $options    Connection options.
     * @param LoopInterface         $loop       The event loop used for the timeout timer.
     * @param FrameParser|null      $parser     The parser used to create AMQP frames from binary data, or null for the default.
     * @param FrameSerializer|null  $serializer The serialize used to create binary data from AMQP frames, or null for the default.
     */
    public function __construct(
        DuplexStreamInterface $stream,
        ConnectionOptions $options,
        LoopInterface $loop,
        FrameParser $parser,
        FrameSerializer $serializer
    ) {
        $this->options = $options;
        $this->stream = $stream;
        $this->loop = $loop;
        $this->parser = $parser;
        $this->serializer = $serializer;
        $this->state = self::STATE_SEND_HEADER;
    }

    /**
     * Perform the AMQP handshake.
     *
     * Via promise:
     * @return tuple<ConnectionStartFrame, ConnectionTuneFrame>
     * @throws ConnectionException
     * @throws ProtocolException
     */
    public function start()
    {
        if (self::STATE_SEND_HEADER !== $this->state) {
            throw new LogicException('The AMQP handshake has already been started.');
        } elseif (!$this->stream->isWritable()) {
            throw new LogicException('The stream is not writable.');
        }

        // Create a timer that will abort the handshake if it is not completed
        // in time ...
        $this->timeoutTimer = $this->loop->addTimer(
            $this->options->timeout(),
            [$this, 'handshakeTimedOut']
        );

        // Connect stream handlers, use public methods so they can be removed easily ...
        $this->stream->on('data',  [$this, 'streamData']);
        $this->stream->on('close', [$this, 'streamClosed']);
        $this->stream->on('error', [$this, 'handshakeFailed']);

        // Send the AMQP v0.9.1 protocol header and wait for a response ...
        $this->stream->write("AMQP\x00\x00\x09\x01");
        $this->stream->resume();
        $this->state = self::STATE_WAIT_START;

        $this->deferred = new Deferred([$this, 'cancel']);

        return $this->deferred->promise();
    }

    /**
     * Cancel the handshake process.
     *
     * This method is invoked if the promise returned by run() is cancelled.
     */
    public function cancel()
    {
        if (self::STATE_SEND_HEADER === $this->state) {
            throw new LogicException('The AMQP handshake has not been started.');
        } elseif (self::STATE_DONE === $this->state) {
            throw new LogicException('The AMQP handshake has process has already completed (or failed).');
        }

        $this->handshakeFailed(
            ConnectionException::handshakeFailed(
                $this->options,
                'The AMQP handhake was cancelled by the client.'
            )
        );
    }

    /**
     * Respond to the Connection.Start frame.
     *
     * @param ConnectionStartFrame $frame
     */
    private function handshakeStart(ConnectionStartFrame $frame)
    {
        if (!preg_match('/\bAMQPLAIN\b/', $frame->mechanisms)) {
            throw ConnectionException::handshakeFailed(
                $this->options,
                'The AMQP server does not support the AMQPLAIN authentication mechanism.'
            );
        }

        $this->stream->write(
            $this->serializer->serialize(
                ConnectionStartOkFrame::create(
                    0,
                    [
                        'product'     => $this->options->productName(),
                        'version'     => $this->options->productVersion(),
                        'platform'    => PackageInfo::AMQP_PLATFORM,
                        'copyright'   => PackageInfo::AMQP_COPYRIGHT,
                        'information' => PackageInfo::AMQP_INFORMATION,
                    ],
                    'AMQPLAIN',
                    $this->serializer->serializeAmqPlainCredentials(
                        $this->options->username(),
                        $this->options->password()
                    )
                )
            )
        );

        $this->state = self::STATE_WAIT_TUNE;
        $this->startFrame = $frame;
    }

    /**
     * Respond to the Connection.Tune frame.
     *
     * @param ConnectionTuneFrame $frame
     */
    private function handshakeTune(ConnectionTuneFrame $frame)
    {
        // server supports "unlimited" channels ...
        if ($frame->channelMax === 0) {
            $channelMax = self::MAX_USER_CHANNELS;

        // or otherwise supports more than us ...
        } elseif (self::MAX_USER_CHANNELS < $frame->channelMax) {
            $channelMax = self::MAX_USER_CHANNELS;

        // otherwise accept server suggestion ...
        } else {
            $channelMax = $frame->channelMax;
        }

        $this->stream->write(
            $this->serializer->serialize(
                ConnectionTuneOkFrame::create(
                    0,
                    $channelMax,
                    $frame->frameMax,
                    $frame->heartbeat
                )
            )
        );

        $this->stream->write(
            $this->serializer->serialize(
                ConnectionOpenFrame::create(
                    0,
                    $this->options->vhost()
                )
            )
        );

        $this->state = self::STATE_WAIT_OPEN_OK;
        $this->tuneFrame = $frame;
    }

    /**
     * Respond to the Connection.Open-Ok frame.
     *
     * @param ConnectionOpenOkFrame $frame
     */
    private function handshakeOpen(ConnectionOpenOkFrame $frame)
    {
        $this->stream->pause();
        $this->stream->removeListener('data',  [$this, 'streamData']);
        $this->stream->removeListener('close', [$this, 'streamClosed']);
        $this->stream->removeListener('error', [$this, 'handshakeFailed']);

        $this->state = self::STATE_DONE;

        $this->timeoutTimer->cancel();

        $this->deferred->resolve(
            [$this->startFrame, $this->tuneFrame]
        );
    }

    /**
     * Abort the handshake process due to an error.
     *
     * @param Exception $exception
     */
    public function handshakeFailed(Exception $exception)
    {
        $this->state = self::STATE_DONE;
        $this->deferred->reject($exception);
        $this->stream->close();
    }

    /**
     * Abort the handshake process due to a timeout.
     *
     * @param Exception $exception
     */
    public function handshakeTimedOut()
    {
        $this->handshakeFailed(
            ConnectionException::handshakeFailed(
                $this->options,
                'Handshake timed out after ' . $this->options->timeout() . ' seconds.'
            )
        );
    }

    /**
     * Respond to data received from the stream.
     *
     * @param string $data
     */
    public function streamData($data)
    {
        try {
            foreach ($this->parser->feed($data) as $frame) {
                if (self::STATE_WAIT_START === $this->state && $frame instanceof ConnectionStartFrame) {
                    $this->handshakeStart($frame);
                } elseif (self::STATE_WAIT_TUNE === $this->state && $frame instanceof ConnectionTuneFrame) {
                    $this->handshakeTune($frame);
                } elseif (self::STATE_WAIT_OPEN_OK === $this->state && $frame instanceof ConnectionOpenOkFrame) {
                    $this->handshakeOpen($frame);
                } else {
                    throw ProtocolException::create(
                        sprintf(
                            'Unexpected frame received during AMQP handshake (%s).',
                            get_class($frame)
                        )
                    );
                }
            }
        } catch (Exception $e) {
            $this->handshakeFailed($e);
        }
    }

    /**
     * Respond to the stream being closed.
     */
    public function streamClosed()
    {
        if (self::STATE_DONE === $this->state) {
            return;
        } elseif (self::STATE_WAIT_TUNE === $this->state) {
            $exception = ConnectionException::authenticationFailed($this->options);
        } elseif (self::STATE_WAIT_OPEN_OK === $this->state) {
            $exception = ConnectionException::authorizationFailed($this->options);
        } else {
            $exception = ConnectionException::closedUnexpectedly($this->options);
        }

        $this->deferred->reject($exception);
        $this->state = self::STATE_DONE;
    }

    /**
     * The maximum time to wait AT EACH STEP of the handshake process.
     */
    const HANDSHAKE_TIMEOUT = 3;

    const STATE_SEND_HEADER  = 0;
    const STATE_WAIT_START   = 1;
    const STATE_WAIT_TUNE    = 2;
    const STATE_WAIT_OPEN_OK = 3;
    const STATE_DONE         = 4; // done, either successful or not

    const MAX_USER_CHANNELS = 0xfffe;     // 2-byte channel ID, but zero is reserved for connection-level communication.

    /**
     * @var DuplexStreamInterface The stream to use for communication, typically a TCP connection.
     */
    private $stream;

    /**
     * @var ConnectionOptions
     */
    private $options;

    /**
     * @var FrameParser The parser used to create AMQP frames from binary data, or null for the default.
     */
    private $parser;

    /**
     * @var LoopInterface The event loop used for the timeout timer.
     */
    private $loop;

    /**
     * @var FrameSerializer The serialize used to create binary data from AMQP frames, or null for the default.
     */
    private $serializer;

    /**
     * @var integer The current state of the handshake, one of the self::STATE_* constants.
     */
    private $state;

    /**
     * @var Deferred The deferred that is resolved when the handshake is complete.
     */
    private $deferred;

    /**
     * @var TimerInterface|null The timeout timer.
     */
    private $timeoutTimer;

    /**
     * @var ConnectionStartFrame|null
     */
    private $startFrame;

    /**
     * @var ConnectionTuneFrame|null
     */
    private $tuneFrame;
}
