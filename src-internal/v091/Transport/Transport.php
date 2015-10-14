<?php

namespace Recoil\Amqp\v091\Transport;

use Recoil\Amqp\v091\Protocol\OutgoingFrame;

/**
 * Send and receive AMQP frames.
 */
interface Transport
{
    /**
     * Resume (or start) listening to transport events.
     *
     * @param TransportController $controller The controller that is managing the transport.
     */
    public function resume(TransportController $controller);

    /**
     * Temporarily stop listening to transport events.
     */
    public function pause();

    /**
     * Send a frame.
     *
     * @param OutgoingFrame $frame The frame to send.
     */
    public function send(OutgoingFrame $frame);

    /**
     * Permanently stop listening to transport events and close the transport.
     */
    public function close();
}
