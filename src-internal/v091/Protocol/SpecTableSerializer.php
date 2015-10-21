<?php

namespace Recoil\Amqp\v091\Protocol;

use InvalidArgumentException;

/**
 * Serialize an AMQP table to a string buffer.
 *
 * This implementation uses the field types as discussed in the AMQP 0.9.1
 * specification. It is NOT suitable for use with RabbitMQ or Qpid, and possibly
 * other brokers.
 *
 * @see SigTableSerializer for an implementation suitable for RabbitMQ and Qpid.
 *
 * @todo Implement this!
 */
final class SpecTableSerializer implements TableSerializer
{
    /**
     * Serialize an AMQP table.
     *
     * @param array $table The table.
     *
     * @return string                   The binary serialized table.
     * @throws InvalidArgumentException if the table contains unserializable
     *                                  values.
     */
    public function serialize(array $table)
    {
        throw new \LogicException('Not implemented.');
    }
}
