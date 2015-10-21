<?php

namespace Recoil\Amqp\v091\Protocol;

use Recoil\Amqp\Exception\ProtocolException;

/**
 * Parse an AMQP table from a string buffer.
 *
 * This implementation uses the field types as discussed in the AMQP 0.9.1
 * specification. It is NOT suitable for use with RabbitMQ or Qpid, and possibly
 * other brokers.
 *
 * @see SigTableParser for an implementation suitable for RabbitMQ and Qpid.
 *
 * @todo Implement this!
 */
final class SpecTableParser implements TableParser
{
    /**
     * Retrieve the next frame from the internal buffer.
     *
     * @param string &$buffer Binary data containing the table.
     *
     * @return array             The table.
     * @throws ProtocolException if the incoming data does not conform to the
     *                           AMQP specification.
     */
    public function parse(&$buffer)
    {
        throw new \LogicException('Not implemented.');
    }
}
