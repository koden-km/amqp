<?php

namespace Recoil\Amqp\v091\Transport;

use Exception;
use Recoil\Amqp\v091\Protocol\IncomingFrame;
use Recoil\Amqp\v091\Protocol\OutgoingFrame;

/**
 * A high-level interface for frame-based communiation with an AMQP server.
 */
interface ServerApi
{
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
}
