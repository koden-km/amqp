<?php

namespace Recoil\Amqp\v091\Transport;

use Exception;
use Recoil\Amqp\v091\Protocol\IncomingFrame;

/**
 * A controller manages communication over a transport.
 */
interface TransportController
{
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
    public function start(Transport $transport);

    /**
     * Notify the controller of an incoming frame.
     *
     * @param IncomingFrame $frame The received frame.
     *
     * @throws Exception The implementation may throw any exception, which closes
     *                   the transport.
     */
    public function onFrame(IncomingFrame $frame);

    /**
     * Notify the controller that the transport has been closed.
     *
     * @param Exception|null $exception The error that caused the closure, if any.
     */
    public function onTransportClosed(Exception $exception = null);
}
