<?php

namespace Recoil\Amqp;

use Evenement\EventEmitterInterface;
use Recoil\Amqp\Exception\ConnectionException;

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
     * @return Channel             [via promise] The newly created channel.
     * @throws ConnectionException [via promise] If not connected to the AMQP server.
     */
    public function channel();

    /**
     * Disconnect from the server.
     */
    public function close();
}
