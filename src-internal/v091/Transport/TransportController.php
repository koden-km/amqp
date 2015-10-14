<?php

namespace Recoil\Amqp\v091\Transport;

use Exception;
use React\Promise\PromiseInterface;
use Recoil\Amqp\Exception\ConnectionException;
use Recoil\Amqp\Exception\ProtocolException;
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
     * @return PromiseInterface There is no restriction placed on how a controller resolves, rejects or notifies the promise.
     */
    public function start(Transport $transport);

    /**
     * Notify the controller of an incoming frame.
     *
     * @param IncomingFrame $frame The received frame.
     *
     * @throws Exception The implementation may throw any exception, which closes the transport.
     */
    public function onFrame(IncomingFrame $frame);

    /**
     * Notify the controller that the transport has been closed.
     *
     * @param Exception|null $exception The error that caused the closure, if any.
     */
    public function onTransportClosed(Exception $exception = null);
}
