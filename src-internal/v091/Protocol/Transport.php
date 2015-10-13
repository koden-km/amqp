<?php

namespace Recoil\Amqp\v091\Protocol;

use Exception;
use Recoil\Amqp\ConnectionOptions;

/**
 * A transport facilitates sending and receiving AMQP frames.
 *
 * Before frames can be transmitted the handshake must be completed. Heartbeat
 * frames are managed automatically by the transport.
 */
interface Transport
{
    /**
     * Start the transport.
     *
     * @param ConnectionOptions $options           The options used when establishing the connection.
     * @param integer           $heartbeatInterval The heartbeat interval, as negotiated during the AMQP handshake. May be lower than the value in the connection options.
     */
    public function start(ConnectionOptions $options, $heartbeatInterval);

    /**
     * Send a frame.
     *
     * @param OutgoingFrame $frame The frame to send.
     */
    public function send(OutgoingFrame $frame);

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
    public function wait($type, $channel = 0);

    /**
     * Receive notification when a frame of a given type is received.
     *
     * @param string  $type    The type of frame (the PHP class name).
     * @param integer $channel The channel on which to wait, or null for any channel.
     *
     * Via promise:
     * @return null If the transport or channel is closed cleanly.
     * @notify IncomingFrame For each matching frame that is received, unless it was matched to a previous call to wait().
     * @throws Exception If the transport or channel is closed unexpectedly.
     */
    public function listen($type, $channel = 0);

    /**
     * Close the transport cleanly via AMQP close negotation.
     */
    public function close();
}
