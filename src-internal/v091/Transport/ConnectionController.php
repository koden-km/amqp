<?php

namespace Recoil\Amqp\v091\Transport;

use Exception;
use function React\Promise\reject;
use function React\Promise\resolve;
use LogicException;
use React\EventLoop\LoopInterface;
use React\EventLoop\Timer\TimerInterface;
use React\Promise\Deferred;
use Recoil\Amqp\ConnectionOptions;
use Recoil\Amqp\Exception\ConnectionException;
use Recoil\Amqp\v091\Protocol\IncomingFrame;
use Recoil\Amqp\v091\Protocol\OutgoingFrame;

/**
 * A transport controller that manages a transport which has already completed a
 * successful AMQP handshake.
 */
final class ConnectionController implements TransportController, ServerApi
{
    /**
     * @param LoopInterface     $loop            The event loop used for the heartbeat
     *                                           timer.
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
        $this->state = self::STATE_STARTABLE;
        $this->waiters = [];
        $this->listeners = [];
    }

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

        if (null !== $this->handshakeResult->heartbeatInterval) {
            $this->timer = $this->loop->addPeriodicTimer(
                $this->handshakeResult->heartbeatInterval,
                [$this, 'onHeartbeat']
            );
        }

        $this->transport = $transport;
        $this->transport->resume($this);

        $this->state = self::STATE_OPEN;

        return resolve($this);
    }

    /**
     * Send a frame.
     *
     * @param OutgoingFrame $frame The frame to send.
     */
    public function send(OutgoingFrame $frame)
    {
        $this->heartbeatsSinceLastSend = 0;

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
     * @param string  $type    The type of frame (the PHP class name).
     * @param integer $channel The channel on which to wait, or null for any channel.
     *
     * @return IncomingFrame [via promise] When the next matching frame is received.
     * @throws Exception     [via promise] If the transport or channel is closed.
     */
    public function wait($type, $channel = 0)
    {
        if (self::STATE_OPEN !== $this->state) {
            throw new LogicException('Controller is not open.');
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
     * @param string  $type    The type of frame (the PHP class name).
     * @param integer $channel The channel on which to wait, or null for any channel.
     *
     * @notify IncomingFrame For each matching frame that is received, unless it
     *                       was matched a "waiter" registered via wait().
     *
     * @return null      [via promise] If the transport or channel is closed cleanly.
     * @throws Exception [via promise] If the transport or channel is closed unexpectedly.
     */
    public function listen($type, $channel = 0)
    {
        if (self::STATE_OPEN !== $this->state) {
            throw new LogicException('Controller is not open.');
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
     * Get the server capabilities.
     *
     * @return ServerCapabilities
     */
    public function capabilities()
    {
        throw new LogicException('Not implemented.');
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
        $this->heartbeatsSinceLastReceive = 0;

        if (self::STATE_OPEN === $this->state) {
            $type = get_class($frame);

            if (isset($this->waiters[$frame->channel][$type])) {
                $deferred = array_shift($this->waiters[$frame->channel][$type]);
                $deferred->resolve($frame);
            } elseif (isset($this->listeners[$frame->channel][$type])) {
                foreach ($this->listeners[$frame->channel][$type] as $deferred) {
                    $deferred->notify($frame);
                }
            }

            // TODO
            if ($frame instanceof ConnectionCloseFrame) {
                $this->closedByServer($frame);
                break;
            }
        } elseif (self::STATE_CLOSING === $this->state) {
            // TODO
            if ($frame instanceof ConnectionCloseOkFrame) {
                $this->closedByClient($frame);
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
        $this->done();
        $this->rejectListeners(
            ConnectionException::closedUnexpectedly(
                $this->options,
                $exception
            )
        );
    }

    /**
     * @access private
     */
    public function onHeartbeat()
    {
        if (++$this->heartbeatsSinceLastSend >= 1) {
            $this->send(HeartbeatFrame::create());
        }

        if (++$this->heartbeatsSinceLastReceive >= 2) {
            $this->done();
            $this->rejectListeners(
                ConnectionException::heartbeatTimedOut(
                    $this->options,
                    $this->heartbeatTimer->getInterval()
                )
            );

            $this->transport->close();
        }
    }

    private function done()
    {
        $this->state = self::STATE_CLOSED;

        if ($this->timer) {
            $this->timer->cancel();
            $this->timer = null;
        }
    }

    /**
     * Resolve/reject all pending waiters/listeners.
     *
     * @param Exception|null $exception The rejection exception, if any.
     */
    private function finalizeListeners(Exception $exception = null)
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

    /**
     * The controller is ready to be started.
     */
    const STATE_STARTABLE = 0;

    /**
     * The controller has been started.
     */
    const STATE_OPEN = 1;

    /**
     * The server has initiated a graceful closure.
     */
    const STATE_SERVER_CLOSING = 2;

    /**
     * The client has initiated a graceful closure.
     */
    const STATE_CLIENT_CLOSING = 3;

    /**
     * The connection has been closed.
     */
    const STATE_CLOSED = 4;

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
    private $state;

    /**
     * @var array A 2-dimensional array mapping channel/frame type to a queue of
     *            deferreds ($waiters[$channel][$frameType] === [$deferred, ...]).
     */
    private $waiters;

    /**
     * @var array A 2-dimensional array mapping channel/frame to a sequence of
     *            deferreds ($waiters[$channel][$frameType] === [$deferred, ...]).
     */
    private $listeners;

    /**
     * @var Transport The transport that this controller is managing.
     */
    private $transport;

    /**
     * @var TimerInterface|null The heartbeat timer, if heartbeat is enabled.
     */
    private $timer;

    /**
     * @var integer The number of heartbeat ticks that have occurred since data
     *              was last sent to the server.
     */
    private $heartbeatsSinceLastSend;

    /**
     * @var integer The number of heartbeat ticks that have occurred since data
     *              was last received from the server.
     */
    private $heartbeatsSinceLastReceive;
}
