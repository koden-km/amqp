<?php
namespace Recoil\Amqp;

/**
 * Establishes a connection to an AMQP server.
 */
interface Connector
{
    /**
     * Connect to an AMQP server.
     *
     * Via promise:
     * @return Connection          The AMQP connection.
     * @throws ConnectionException if the connection could not be established.
     */
    public function connect();
}