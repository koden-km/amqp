<?php

namespace Recoil\Amqp\v091\Protocol;

use Recoil\Amqp\Exception\ProtocolException;

/**
 * Parse an AMQP table from a string buffer.
 */
interface TableParser
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
    public function parse(&$buffer);
}
