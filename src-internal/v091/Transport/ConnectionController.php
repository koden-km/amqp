<?php

namespace Recoil\Amqp\v091\Transport;

use Exception;
use function React\Promise\reject;
use function React\Promise\resolve;
use LogicException;
use React\EventLoop\LoopInterface;
use React\EventLoop\Timer\TimerInterface;
use Recoil\Amqp\ConnectionOptions;
use Recoil\Amqp\Exception\ChannelException;
use Recoil\Amqp\Exception\ConnectionException;
use Recoil\Amqp\ServerCapabilities;
use Recoil\Amqp\v091\Protocol\Channel\ChannelCloseFrame;
use Recoil\Amqp\v091\Protocol\Channel\ChannelCloseOkFrame;
use Recoil\Amqp\v091\Protocol\Channel\ChannelOpenFrame;
use Recoil\Amqp\v091\Protocol\Channel\ChannelOpenOkFrame;
use Recoil\Amqp\v091\Protocol\Connection\ConnectionCloseFrame;
use Recoil\Amqp\v091\Protocol\Connection\ConnectionCloseOkFrame;
use Recoil\Amqp\v091\Protocol\HeartbeatFrame;
use Recoil\Amqp\v091\Protocol\IncomingFrame;
use Recoil\Amqp\v091\Protocol\OutgoingFrame;
use RuntimeException;

/**
 * A transport controller that manages a transport which has already completed a
 * successful AMQP handshake.
 */
final class ConnectionController implements TransportController, ServerApi
{
    /**
     * @param LoopInterface     $loop            The event loop used for the
     *                                           heartbeat timer.
     * @param ConnectionOptions $options         The options used when establishing
     *                                           the connection.
     * @param HandshakeResult   $handshakeResult The result of the AMQP handshake.
     */
    public function __construct(
        LoopInterface $loop,
        ConnectionOptions $options,
        HandshakeResult $handshakeResult
    ) {
        $this->loop = $loop;
        $this->options = $options;
        $this->handshakeResult = $handshakeResult;
    }

    ////////////////////////////////////////
    // TransportController Implementation //
    ////////////////////////////////////////

    /**
     * Begin managing a transport.
     *
     * @param Transport $transport The transport to manage.
     *
     * @return mixed          [via promise] If the controller's work is completed
     *                        successfully (implementation defined).
     * @throws Exception      [via promise] If the controller encounters an error
     *                        (implementation defined).
     * @throws LogicException If the controller has been started previously.
     */
    public function start(Transport $transport)
    {
        if (self::STATE_STARTABLE !== $this->state) {
            throw new LogicException('Controller has already been started.');
        }

        // Start a heartbeat timer if the heartbeat is enabled ...
        if (null !== $this->handshakeResult->heartbeatInterval) {
            $this->heartbeatTimer = $this->loop->addPeriodicTimer(
                $this->handshakeResult->heartbeatInterval,
                [$this, 'onHeartbeat']
            );
        }

        $this->transport = $transport;
        $this->transport->resume($this);

        $this->state = self::STATE_OPEN;
        $this->channels[0] = new ConnectionControllerChannel(0);

        return resolve($this);
    }

    /**
     * Notify the controller of an incoming frame.
     *
     * @param IncomingFrame $frame The received frame.
     *
     * @throws Exception The implementation may throw any exception, which closes
     *                   the transport.
     */
    public function onFrame(IncomingFrame $frame)
    {
        $this->heartbeatsSinceFrameReceived = 0;

        if (self::STATE_OPEN === $this->state) {

            // A new channel was opened successfully ...
            if ($frame instanceof ChannelOpenOkFrame) {
                $this->channels[$frame->frameChannelId]->onOpen();

            // A channel was closed by the server ...
            } elseif ($frame instanceof ChannelCloseFrame) {
                $channel = $this->channels[$frame->frameChannelId];
                unset($this->channels[$frame->frameChannelId]);

                $channel->onClose(
                    // @todo use more meaningful exception
                    // @see https://github.com/recoilphp/amqp/issues/15
                    ChannelException::closedUnexpectedly(
                        $frame->frameChannelId,
                        new RuntimeException(
                            $frame->replyText,
                            $frame->replyCode
                        )
                    )
                );

            // A channel was closed cleanly by the client ...
            } elseif ($frame instanceof ChannelCloseOkFrame) {
                $channel = $this->channels[$frame->frameChannelId];
                unset($this->channels[$frame->frameChannelId]);

                $channel->onClose();

            // The connection was closed by the server ...
            } elseif ($frame instanceof ConnectionCloseFrame) {
                $this->state = self::STATE_CLOSING;

                $this->transport->send(ConnectionCloseOkFrame::create());
                $this->transport->close();

                $this->allChannelsClosed(
                    // @todo use more meaningful exception
                    // @see https://github.com/recoilphp/amqp/issues/15
                    ConnectionException::closedUnexpectedly(
                        $this->options,
                        new RuntimeException(
                            $frame->replyText,
                            $frame->replyCode
                        )
                    )
                );

            // Any other channel frame is dispatched to the channel's
            // waiters/listeners ...
            } else {
                $this->channels[$frame->frameChannelId]->dispatch($frame);
            }

        // When the connection is closing, any frames other than an acknowledgement
        // of the closure are ignored ...
        } elseif (self::STATE_CLOSING === $this->state) {
            if ($frame instanceof ConnectionCloseOkFrame) {
                $this->transport->close();
            }
        }
    }

    /**
     * Notify the controller that the transport has been closed.
     *
     * @param Exception|null $exception The error that caused the closure, if any.
     */
    public function onTransportClosed(Exception $exception = null)
    {
        if ($this->heartbeatTimer) {
            $this->heartbeatTimer->cancel();
            $this->heartbeatTimer = null;
        }

        if ($this->closeTimeoutTimer) {
            $this->closeTimeoutTimer->cancel();
            $this->closeTimeoutTimer = null;
        }

        // We were expecting a close, all waiters/listeners have already been
        // settled ...
        if (self::STATE_CLOSING === $this->state) {
            $this->state = self::STATE_CLOSED;

        // An unexpected closure, settle all waiters/listeners with an
        // exception ...
        } else {
            $this->state = self::STATE_CLOSED;

            $this->allChannelsClosed(
                ConnectionException::closedUnexpectedly(
                    $this->options,
                    $exception
                )
            );
        }
    }

    /**
     * @access private
     */
    public function onHeartbeat()
    {
        if ($this->sendHeartbeatFrame) {
            $this->transport->send(HeartbeatFrame::create());
        } else {
            $this->sendHeartbeatFrame = true;
        }

        if (++$this->heartbeatsSinceFrameReceived >= 2) {
            $this->state = self::STATE_CLOSING;
            $this->transport->close();

            $this->allChannelsClosed(
                    ConnectionException::heartbeatTimedOut(
                    $this->options,
                    $this->handshakeResult->heartbeatInterval
                )
            );
        }
    }

    /**
     * Notify all channels of closure.
     *
     * @param Exception|null $exception The exception that caused the closure, if any.
     */
    private function allChannelsClosed(Exception $exception = null)
    {
        $channels = $this->channels;
        $this->channels = [];

        foreach ($channels as $channel) {
            $channel->onClose($exception);
        }
    }

    //////////////////////////////
    // ServerApi Implementation //
    //////////////////////////////

    /**
     * Send a frame.
     *
     * @param OutgoingFrame $frame The frame to send.
     */
    public function send(OutgoingFrame $frame)
    {
        if (self::STATE_OPEN !== $this->state) {
            throw ConnectionException::notOpen($this->options);
        }

        $this->sendHeartbeatFrame = false;

        $this->transport->send($frame);
    }

    /**
     * Wait for the next frame of a given type.
     *
     * This method is generally used to wait for a response from the server after
     * sending a "synchronous" method frame (i.e, one with a matching "OK" frame).
     *
     * The "waiter" is pushed on to a channel/frame-type specific queue. When a
     * matching frame is received the first waiter is popped from the queue and
     * resolved using the frame as the value. If the queue is empty, any "listeners"
     * registered for the same channel/frame-type are notified of the frame.
     *
     * @see ServerApi::listen() To register a listener that is notified of every
     *                          received frame of a given type.
     *
     * @param string  $type      The type of frame (the PHP class name).
     * @param integer $channelId The channel on which to wait, or null for any channel.
     *
     * @return IncomingFrame       [via promise] When the next matching frame is received.
     * @throws ChannelException    [via promise] If the channel is closed.
     * @throws ConnectionException [via promise] If the connection is closed.
     */
    public function wait($type, $channelId = 0)
    {
        if (self::STATE_OPEN !== $this->state) {
            return reject(ConnectionException::notOpen($this->options));
        } elseif (!isset($this->channels[$channelId])) {
            return reject(ChannelException::notOpen($channelId));
        }

        return $this->channels[$channelId]->waitForFrameType($type);
    }

    /**
     * Receive notification when frames of a given type are received.
     *
     * This method is generally used to receive asynchronous/push style
     * notifications from the server.
     *
     * The "listener" is added to channel/frame-type specific pool. When a matching
     * frame is received that is not dispatched to one of the registered "waiters",
     * each listener is notified using the frame as the value.
     *
     * @see ServerApi::wait() To register a one-time "waiter" that intercepts
     *                        an incoming frame before it is dispathed to the "listeners".
     *
     * @param string  $type      The type of frame (the PHP class name).
     * @param integer $channelId The channel on which to wait, or null for any channel.
     *
     * @notify IncomingFrame For each matching frame that is received, unless it
     *                       was matched a "waiter" registered via wait().
     *
     * @return null                [via promise] If the transport or channel is closed cleanly.
     * @throws ChannelException    [via promise] If the channel is closed.
     * @throws ConnectionException [via promise] If the connection is closed.
     */
    public function listen($type, $channelId = 0)
    {
        if (self::STATE_OPEN !== $this->state) {
            return reject(ConnectionException::notOpen($this->options));
        } elseif (!isset($this->channels[$channelId])) {
            return reject(ChannelException::notOpen($channelId));
        }

        return $this->channels[$channelId]->listenForFrameType($type);
    }

    /**
     * Get the server capabilities.
     *
     * @return ServerCapabilities
     */
    public function capabilities()
    {
        return $this->handshakeResult->serverCapabilities;
    }

    /**
     * Open a channel.
     *
     * @return integer             [via promise] The channel ID.
     * @throws ChannelException    [via promise] If the channel could not be opened.
     * @throws ConnectionException [via promise] If the connection is closed.
     */
    public function openChannel()
    {
        if (self::STATE_OPEN !== $this->state) {
            return reject(ConnectionException::notOpen($this->options));
        }

        $channelId = $this->findUnusedChannelId();

        if (null === $channelId) {
            return reject(ChannelException::noAvailableChannels());
        }

        $channel = new ConnectionControllerChannel($channelId);
        $this->channels[$channelId] = $channel;
        $promise = $channel->waitForOpen();

        $this->transport->send(ChannelOpenFrame::create($channelId));

        return $promise;
    }

    /**
     * Close a channel.
     *
     * Any waiters/listeners for this channel are settled.
     *
     * @param integer $channelId The channel ID.
     *
     * @return null                [via promise] On success.
     * @throws ChannelException    [via promise] If the channel is closed.
     * @throws ConnectionException [via promise] If the connection is closed.
     */
    public function closeChannel($channelId)
    {
        if (self::STATE_OPEN !== $this->state) {
            return reject(ConnectionException::notOpen($this->options));
        } elseif (!isset($this->channels[$channelId])) {
            return reject(ChannelException::notOpen($channelId));
        }

        $promise = $this->channels[$channelId]->waitForClose();

        $this->transport->send(ChannelCloseFrame::create($channelId));

        return $promise;
    }

    /**
     * Close the connection.
     */
    public function close()
    {
        if ($this->state !== self::STATE_OPEN) {
            return;
        }

        $this->closeTimeoutTimer = $this->loop->addTimer(
            self::CLOSE_TIMEOUT,
            [$this->transport, 'close']
        );

        $this->state = self::STATE_CLOSING;
        $this->allChannelsClosed();
        $this->transport->send(ConnectionCloseFrame::create());
    }

    /**
     * Find an unused channel ID.
     *
     * @return integer|null The channel ID, or null if none are available.
     */
    private function findUnusedChannelId()
    {
        // first check in range [next, max] ...
        $max = $this->handshakeResult->maximumChannelCount;

        for ($channelId = $this->nextChannelId; $channelId <= $max; ++$channelId) {
            if (!isset($this->channels[$channelId])) {
                $this->nextChannelId = $channelId + 1;

                return $channelId;
            }
        }

        // then check in range [min, next) ...
        for ($channelId = 1; $channelId < $this->nextChannelId; ++$channelId) {
            if (!isset($this->channels[$channelId])) {
                $this->nextChannelId = $channelId + 1;

                return $channelId;
            }
        }

        // channel IDs are exhausted ...
        return null;
    }

    /**
     * The controller is ready to be started.
     */
    const STATE_STARTABLE = 0;

    /**
     * The controller has been started.
     */
    const STATE_OPEN = 1;

    /**
     * The close handshake has begin.
     */
    const STATE_CLOSING = 2;

    /**
     * The connection has been closed.
     */
    const STATE_CLOSED = 3;

    /**
     * The maximum time (in seconds) to wait for the server to respond to a
     * close frame before forcefully closing the connection.
     */
    const CLOSE_TIMEOUT = 5;

    /**
     * @var LoopInterface The event loop used for the heartbeat timer
     */
    private $loop;

    /**
     * @var ConnectionOptions The options used when establishing the connection.
     */
    private $options;

    /**
     * @var HandshakeResult The result of the AMQP handshake.
     */
    private $handshakeResult;

    /**
     * @var integer The current state of the controller; one of the self::STATE_*
     *              constants.
     */
    private $state = self::STATE_STARTABLE;

    /**
     * @var array<integer, ConnectionControllerChannel> The set of open[ing] channels.
     *                     The key is the channel ID.
     */
    private $channels = [];

    /**
     * @var integer The next (probably) available channel ID.
     */
    private $nextChannelId = 1;

    /**
     * @var Transport The transport that this controller is managing.
     */
    private $transport;

    /**
     * @var TimerInterface|null The timer used to force close the connection if
     *                          the server does not respond to close frames in
     *                          a timely manner.
     */
    private $closeTimeoutTimer;

    /**
     * @var TimerInterface|null The heartbeat timer, if heartbeat is enabled.
     */
    private $heartbeatTimer;

    /**
     * @var boolean True if a heartbeat frame should be sent on the next heartbeat.
     */
    private $sendHeartbeatFrame = true;

    /**
     * @var integer The number of heartbeat ticks that have occurred since data
     *              was last received from the server.
     */
    private $heartbeatsSinceFrameReceived = 0;
}
