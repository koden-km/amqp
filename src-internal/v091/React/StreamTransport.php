<?php
namespace Recoil\Amqp\v091\React;

use Exception;
use React\EventLoop\LoopInterface;
use React\Promise\Deferred;
use React\Stream\DuplexStreamInterface;
use Recoil\Amqp\ConnectionException;
use Recoil\Amqp\ConnectionOptions;
use Recoil\Amqp\PackageInfo;
use Recoil\Amqp\v091\Protocol\Connection\ConnectionOpenFrame;
use Recoil\Amqp\v091\Protocol\Connection\ConnectionOpenOkFrame;
use Recoil\Amqp\v091\Protocol\Connection\ConnectionStartFrame;
use Recoil\Amqp\v091\Protocol\Connection\ConnectionStartOkFrame;
use Recoil\Amqp\v091\Protocol\Connection\ConnectionTuneFrame;
use Recoil\Amqp\v091\Protocol\Connection\ConnectionTuneOkFrame;
use Recoil\Amqp\v091\Protocol\FrameParser;
use Recoil\Amqp\v091\Protocol\FrameSerializer;
use Recoil\Amqp\v091\Protocol\HeartbeatFrame;
use Recoil\Amqp\v091\Protocol\OutgoingFrame;
use Recoil\Amqp\v091\Protocol\Transport;
use Traversable;
use function React\Promise\resolve;

final class StreamTransport implements Transport
{
    public function __construct(
        DuplexStreamInterface $stream,
        LoopInterface $loop,
        FrameParser $parser = null,
        FrameSerializer $serializer = null
    ) {
        $this->stream = $stream;
        $this->loop = $loop;
        $this->parser = $parser ?: new FrameParser();
        $this->serializer = $serializer ?: new FrameSerializer();
        $this->waiters = [];

        $this->stream->on('data', function ($data) {
            $this->heartbeatsWithoutReceive = 0;
            $this->resolveWaiters($this->parser->feed($data));
        });

        $this->stream->on('error', function ($error) {
            $this->rejectWaiters(null, $error);
        });

        $this->stream->on('close', function () {
            if ($this->heartbeatTimer) {
                $this->heartbeatTimer->cancel();
            }
            $this->rejectWaiters(null);
        });
    }

    /**
     * Begin the AMQP handshake.
     *
     * @param ConnectionOptions $options
     *
     * Via promise:
     * @return null
     * @throws ConnectionException
     */
    public function handshake(ConnectionOptions $options)
    {
        $this->stream->write("AMQP\x00\x00\x09\x01");

        return $this
            ->wait(ConnectionStartFrame::class)
            ->then(function ($frame) use ($options) {
                $this->send(
                    ConnectionStartOkFrame::create(
                        0,
                        [
                            'product'     => $options->productName(),
                            'version'     => $options->productVersion(),
                            'platform'    => PackageInfo::AMQP_PLATFORM,
                            'copyright'   => PackageInfo::AMQP_COPYRIGHT,
                            'information' => PackageInfo::AMQP_INFORMATION,
                        ],
                        'AMQPLAIN',
                        $this->serializer->serializePlainCredentials(
                            $options->username(),
                            $options->password()
                        )
                    )
                );

                return $this->wait(ConnectionTuneFrame::class);
            })
            ->otherwise(function (Exception $previous = null) use ($options) {
                throw ConnectionException::authenticationFailed($options, $previous);
            })
            ->then(function ($frame) use ($options) {
                $this->tuningOptions = $frame;

                $this->send(
                    ConnectionTuneOkFrame::create(
                        0,
                        $this->tuningOptions->channelMax,
                        $this->tuningOptions->frameMax,
                        $this->tuningOptions->heartbeat
                    )
                );

                $this->send(
                    ConnectionOpenFrame::create(
                        0,
                        $options->vhost()
                    )
                );

                return $this->wait(ConnectionOpenOkFrame::class);
            })
            ->otherwise(function (Exception $previous = null) use ($options) {
                throw ConnectionException::handshakeFailed($options, $previous);
            })
            ->then(function () {
                $this->heartbeatTimer = $this->loop->addPeriodicTimer(
                    $this->tuningOptions->heartbeat,
                    function () {
                        $this->heartbeat();
                    }
                );
            });
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
     * @param string       $type    The type of frame (the PHP class name).
     * @param integer|null $channel The channel on which to wait, or null for any channel.
     *
     * Via promise:
     * @return IncomingFrame
     * @throws Exception
     */
    public function wait($type, $channel = 0)
    {
        $deferred = new Deferred();
        $this->waiters[$type][] = [$channel, $deferred];

        return $deferred->promise();
    }

    /**
     * Receive notification of frames of a given type.
     *
     * @param string       $type    The type of frame (the PHP class name).
     * @param integer|null $channel The channel on which to wait, or null for any channel.
     *
     * The promise is notified of each incoming frame until it is cancelled.
     * If the transport is closed cleanly the promise is resolved, otherwise it
     * is rejected.
     *
     * Via promise:
     * @return null
     * @notify IncomingFrame
     * @throws Exception
     */
    public function on($type, $channel = 0)
    {
    }

    private function resolveWaiters(Traversable $frames)
    {
        foreach ($frames as $frame) {
            $type = get_class($frame);

            if (isset($this->waiters[$type])) {
                foreach ($this->waiters[$type] as $index => list($channelFilter, $deferred)) {
                    if ($channelFilter === null || $channelFilter === $frame->channel) {
                        unset($this->waiters[$type][$index]);
                        $deferred->resolve($frame);
                    }
                }
            }
        }
    }

    private function rejectWaiters($channel, Exception $error = null)
    {
        foreach ($this->waiters as $type => $waiters) {
            foreach ($waiters as $index => list($channelFilter, $deferred)) {
                if ($channel === null || $channelFilter === null || $channelFilter === $channel) {
                    unset($this->waiters[$type][$index]);
                    $deferred->reject($error);
                }
            }
        }
    }

    private function heartbeat()
    {
        if (++$this->heartbeatsWithoutSend >= 1) {
            $this->send(HeartbeatFrame::create());
        }

        if (++$this->heartbeatsWithoutReceive >= 2) {
            $this->rejectWaiters(
                null,
                ConnectionException::heartbeatExpired($this->tuneOptions->heartbeat)
            );

            $this->stream->close();
        }
    }

    private $stream;
    private $loop;
    private $parser;
    private $serializer;
    private $waiters;
    private $tuningOptions;
    private $heartbeatTimer;
    private $heartbeatsWithoutSend;
    private $heartbeatsWithoutReceive;
}
