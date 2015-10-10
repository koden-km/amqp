<?php
namespace Recoil\Amqp;

use Evenement\EventEmitterInterface;

/**
 * A connection to an AMQP server.
 *
 * @event "close" if AMQP connection is closed.
 */
interface Connection extends EventEmitterInterface
{
    /**
     * Create a new AMQP channel.
     *
     * Via promise:
     * @return Channel             The newly created channel.
     * @throws ConnectionException if not connected to the AMQP server.
     */
    public function channel();

    /**
     * Disconnect from the server.
     *
     * Via promise:
     * @return null
     */
    public function close();
}
