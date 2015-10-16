<?php

namespace Recoil\Amqp;

use Recoil\Amqp\Exception\ConnectionException;

/**
 * Establishes a connection to an AMQP server.
 */
interface Connector
{
    /**
     * Connect to an AMQP server.
     *
     * @param ConnectionOptions $options The options used when establishing the connection.
     *
     * @return Connection          [via promise] The AMQP connection.
     * @throws ConnectionException [via promise] if the connection could not be established.
     */
    public function connect(ConnectionOptions $options);
}
