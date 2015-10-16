<?php

namespace Recoil\Amqp\v091\Transport;

use Exception;
use Recoil\Amqp\v091\Protocol\IncomingFrame;
use Recoil\Amqp\v091\Protocol\OutgoingFrame;

/**
 * A high-level asynchronous interface for frame-based communiation with an
 * AMQP server.
 */
interface ServerApi
{
    /**
     * Send a frame to the server.
     *
     * @param OutgoingFrame $frame The frame to send.
     */
    public function send(OutgoingFrame $frame);

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
    public function wait($type, $channel = 0);

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
    public function listen($type, $channel = 0);

    /**
     * Get the server capabilities.
     *
     * @return ServerCapabilities
     */
    public function capabilities();
}
