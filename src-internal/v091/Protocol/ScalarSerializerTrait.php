<?php

namespace Recoil\Amqp\v091\Protocol;

/**
 * Serialize scalar values from to a binary buffer.
 */
trait ScalarSerializerTrait
{
    /**
     * Serialize a string as an AMQP "short" string.
     *
     * 1-byte length (in bytes), followed by UTF-8 string data.
     *
     * @param string $value The string to serialize.
     *
     * @return string The serialized string.
     */
    private function serializeShortString($value)
    {
        return chr(strlen($value)) . $value;
    }

    /**
     * Serialize a string as an AMQP short string.
     *
     * 4-byte length (in bytes), followed by UTF-8 string data.
     *
     * @param string $value The string to serialize.
     *
     * @return string The serialized string.
     */
    private function serializeLongString($value)
    {
        return pack('N', strlen($value)) . $value;
    }
}
