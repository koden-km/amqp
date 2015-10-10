<?php
namespace Recoil\Amqp\v091\Protocol;

use Recoil\Amqp\ConnectionException;
use Recoil\Amqp\ConnectionOptions;

interface Transport
{
    /**
     * Begin the AMQP handshake.
     *
     * @param ConnectionOptions $options
     *
     * Via promise:
     * @return null
     * @throws ConnectionException
     */
    public function handshake(ConnectionOptions $options);

    /**
     * Send a frame.
     *
     * @param OutgoingFrame $frame The frame to send.
     */
    public function send(OutgoingFrame $frame);

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
    public function wait($type, $channel = 0);

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
    public function on($type, $channel = 0);
}
