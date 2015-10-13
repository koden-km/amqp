<?php
namespace Recoil\Amqp\v091\React;

use Exception;
use React\EventLoop\LoopInterface;
use React\Promise\Deferred;
use React\Stream\DuplexStreamInterface;
use Recoil\Amqp\ConnectionException;
use Recoil\Amqp\ConnectionOptions;
use Recoil\Amqp\ProtocolException;
use Recoil\Amqp\v091\Protocol\Connection\ConnectionCloseFrame;
use Recoil\Amqp\v091\Protocol\Connection\ConnectionCloseOkFrame;
use Recoil\Amqp\v091\Protocol\Constants;
use Recoil\Amqp\v091\Protocol\FrameParser;
use Recoil\Amqp\v091\Protocol\FrameSerializer;
use Recoil\Amqp\v091\Protocol\HeartbeatFrame;
use Recoil\Amqp\v091\Protocol\OutgoingFrame;
use Recoil\Amqp\v091\Protocol\Transport;
use function React\Promise\Timer\timeout;
use function React\Promise\resolve;

/**
 * Send and receive AMQP frames via a React stream.
 *
 * Before frames can be transmitted the handshake must be completed. Heartbeat
 * frames are managed automatically by the transport.
 */
final class StreamTransport implements Transport
{
    /**
     * @param DuplexStreamInterface $stream     The stream to use for communication, typically a TCP connection.
     * @param ConnectionOptions     $options    Connection options.
     * @param LoopInterface         $loop       The event loop used for the timeout timer.
     * @param FrameParser           $parser     The parser used to create AMQP frames from binary data, or null for the default.
     * @param FrameSerializer       $serializer The serialize used to create binary data from AMQP frames, or null for the default.
     */
    public function __construct(
        DuplexStreamInterface $stream,
        ConnectionOptions $options,
        LoopInterface $loop,
        FrameParser $parser,
        FrameSerializer $serializer
    ) {
        $this->stream = $stream;
        $this->options = $options;
        $this->loop = $loop;
        $this->parser = $parser;
        $this->serializer = $serializer;
        $this->state = self::STATE_READY;
        $this->waiters = [];
        $this->listeners = [];
    }

    /**
     * Start the transport.
     */
    public function start($heartbeatInterval)
    {
        // Create time that triggers the heartbeat at regular intervals ...
        $this->heartbeatTimer = $this->loop->addPeriodicTimer(
            $heartbeatInterval,
            [$this, 'checkHeartbeat']
        );

        // Connect stream handlers, use public methods so they can be removed easily ...
        $this->stream->on('data',  [$this, 'streamData']);
        $this->stream->on('close', [$this, 'streamClosed']);
        $this->stream->on('error', [$this, 'streamException']);
        $this->stream->resume();

        $this->state = self::STATE_OPEN;
    }

    /**
     * Send a frame.
     *
     * @param OutgoingFrame $frame The frame to send.
     */
    public function send(OutgoingFrame $frame)
    {
        $this->heartbeatsWithoutSend = 0;

        $this->stream->write(
            $this->serializer->serialize($frame)
        );
    }

    /**
     * Wait for the next frame of a given type.
     *
     * @param string  $type    The type of frame (the PHP class name).
     * @param integer $channel The channel on which to wait, or null for any channel.
     *
     * Via promise:
     * @return IncomingFrame When the next matching frame is received.
     * @throws Exception     If the transport or channel is closed.
     */
    public function wait($type, $channel = 0)
    {
        if (self::STATE_OPEN !== $this->state) {
            throw new LogicException('Transport is not open.');
        }

        $deferred = null;
        $deferred = new Deferred(
            function () use (&$deferred, $channel, $type) {
                $index = array_search(
                    $deferred,
                    $this->waiters[$channel][$type],
                    true
                );

                if (false === $index) {
                    return;
                } elseif (0 === $index) {
                    array_shift($this->waiters[$channel][$type]);
                } else {
                    array_splice(
                        $this->waiters[$channel][$type],
                        $index,
                        1
                    );
                }
            }
        );

        $this->waiters[$channel][$type][] = $deferred;

        return $deferred->promise();
    }

    /**
     * Receive notification when a frame of a given type is received.
     *
     * @param string  $type    The type of frame (the PHP class name).
     * @param integer $channel The channel on which to wait, or null for any channel.
     *
     * Via promise:
     * @return null      If the transport or channel is closed cleanly.
     * @notify IncomingFrame For each matching frame that is received, unless it was matched to a previous call to wait().
     * @throws Exception If the transport or channel is closed unexpectedly.
     */
    public function listen($type, $channel = 0)
    {
        if (self::STATE_OPEN !== $this->state) {
            throw new LogicException('Transport is not open.');
        }

        $index = count($this->waiters[$channel][$type]);
        $deferred = new Deferred(
            function () use ($index, $channel, $type) {
                unset($this->listeners[$channel][$type][$index]);
            }
        );

        $this->listeners[$channel][$type][] = $deferred;

        return $deferred->promise();
    }

    /**
     * Close the transport cleanly via AMQP close negotation.
     */
    public function close()
    {
        if (self::STATE_OPEN !== $this->state) {
            throw new LogicException('Transport is not open.');
        }

        $this->state = self::STATE_CLOSING;

        $this->send(
            ConnectionCloseFrame::create()
        );
    }

    /**
     * Check the heartbeat state.
     */
    public function checkHeartbeat()
    {
        if (++$this->heartbeatsWithoutSend >= 1) {
            $this->send(HeartbeatFrame::create());
        }

        if (++$this->heartbeatsWithoutReceive >= 2) {
            $this->shutdown(
                ConnectionException::heartbeatTimedOut(
                    $this->options,
                    $this->heartbeatTimer->getInterval()
                )
            );

            $this->stream->close();
        }
    }

    /**
     * Respond to data received from the stream.
     *
     * @param string $data
     */
    public function streamData($data)
    {
        $this->heartbeatsWithoutReceive = 0;

        try {
            if (self::STATE_OPEN === $this->state) {
                foreach ($this->parser->feed($data) as $frame) {
                    $type = get_class($frame);

                    if (isset($this->waiters[$frame->channel][$type])) {
                        $deferred = array_shift($this->waiters[$frame->channel][$type]);
                        $deferred->resolve($frame);
                        continue;
                    }

                    if (isset($this->listeners[$frame->channel][$type])) {
                        foreach ($this->listeners[$frame->channel][$type] as $deferred) {
                            $deferred->notify($frame);
                        }
                    }

                    if ($frame instanceof ConnectionCloseFrame) {
                        $this->closedByServer($frame);
                        break;
                    }
                }
            } elseif (self::STATE_CLOSING === $this->state) {
                foreach ($this->parser->feed($data) as $frame) {
                    if ($frame instanceof ConnectionCloseOkFrame) {
                        $this->closedByClient($frame);
                    }
                }
            }
        } catch (ProtocolException $e) {
            $this->rejectListeners($e);
            $this->stream->close();
        }
    }

    /**
     * Respond to the stream being closed.
     */
    public function streamClosed()
    {
        $this->state = self::STATE_CLOSED;

        $this->heartbeatTimer->cancel();

        $this->rejectListeners(
            ConnectionException::closedUnexpectedly($this->options)
        );
    }

    /**
     * Respond to an error from the stream.
     *
     * @param Exception $exception
     */
    public function streamException(Exception $exception)
    {
        $this->rejectListeners(
            ConnectionException::closedUnexpectedly($this->options, $exception)
        );

        $this->stream->close();
    }

    private function closedByServer(ConnectionCloseFrame $frame)
    {
        $this->send(ConnectionCloseOkFrame::create());

        if (Constants::CONNECTION_FORCED === $frame->replyCode) {
            $exception = ConnectionException::closedUnexpectedly($this->options);
        } else {
            // TODO
            $exception = new RuntimeException($frame->replyText, $frame->replyCode);
        }

        $this->rejectListeners($exception);

        $this->stream->close();
    }

    private function closedByClient(ConnectionCloseOkFrame $frame)
    {
        $this->rejectListeners(null);

        $this->stream->close();
    }

    /**
     * Reject all pending waiters and listeners.
     *
     * @param Exception|null $exception The rejection exception, if any.
     */
    private function rejectListeners(Exception $exception = null)
    {
        $waiters = $this->waiters;
        $this->waiters = [];

        foreach ($waiters as $channel => $deferreds) {
            foreach ($deferreds as $deferred) {
                $deferred->reject($exception);
            }
        }

        $listeners = $this->listeners;
        $this->listeners = [];

        foreach ($listeners as $channel => $deferreds) {
            foreach ($deferreds as $deferred) {
                if ($exception) {
                    $deferred->reject($exception);
                } else {
                    $deferred->resolve();
                }
            }
        }
    }

    const STATE_READY   = 0;
    const STATE_OPEN    = 1;
    const STATE_CLOSING = 2;
    const STATE_CLOSED  = 3;

    /**
     * @var DuplexStreamInterface The stream to use for communication, typically a TCP connection.
     */
    private $stream;

    /**
     * @var ConnectionOptions
     */
    private $options;

    /**
     * @var LoopInterface The React event loop to use for timers.
     */
    private $loop;

    /**
     * @var FrameParser The parser used to create AMQP frames from binary data, or null for the default.
     */
    private $parser;

    /**
     * @var FrameSerializer The serialize used to create binary data from AMQP frames, or null for the default.
     */
    private $serializer;

    /**
     * @var integer The current state of the transport, one of the self::STATE_* constants.
     */
    private $state;

    /**
     * @var array<integer, array<string, Deferred>> A 2-dimensional array mapping channel/frame type to a queue of deferreds.
     */
    private $waiters;

    /**
     * @var array<integer, array<string, Deferred>> A 2-dimensional array mapping channel/frame to a sequence of deferreds.
     */
    private $listeners;

    /**
     * @var TimerInterface|null A timer that fires at the heartbeat interval, or null if the state is not OPEN.
     */
    private $heartbeatTimer;

    /**
     * @var integer THe number of heartbeat ticks that have occurred without sending any data.
     */
    private $heartbeatsWithoutSend;

    /**
     * @var integer The number of heartbeat ticks that have occurred without receiving any data.
     */
    private $heartbeatsWithoutReceive;
}
