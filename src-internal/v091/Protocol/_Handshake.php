<?php

namespace Recoil\Amqp\v091\Protocol;

use Recoil\Amqp\ConnectionException;
use Recoil\Amqp\ConnectionOptions;
use Recoil\Amqp\ProtocolException;
use Recoil\Amqp\v091\Protocol\Connection\ConnectionStartFrame;
use Recoil\Amqp\v091\Protocol\Connection\ConnectionTuneFrame;

/**
 * Perform the AMQP handshake.
 *
 * The handshake is a stateful object that is discarded once the handshake is
 * complete.
 */
interface Handshake
{
    /**
     * Perform the AMQP handshake.
     *
     * @param ConnectionOptions $options The options used when establishing the connection.
     *
     * Via promise:
     * @return tuple<ConnectionStartFrame, ConnectionTuneFrame>
     * @throws ConnectionException         If the handshake fails.
     * @throws ProtocolException           If invalid data is received from the server.
     */
    public function start(ConnectionOptions $options);
}
