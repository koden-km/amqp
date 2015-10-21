<?php

namespace Recoil\Amqp\v091\Protocol;

use InvalidArgumentException;

/**
 * Serialize an AMQP table to a string buffer.
 */
interface TableSerializer
{
    /**
     * Serialize an AMQP table.
     *
     * @param array The table.
     *
     * @return string                   The binary serialized table.
     * @throws InvalidArgumentException if the table contains unserializable
     *                                  values.
     */
    public function serialize(array $table);
}
