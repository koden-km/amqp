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

    /**
     * Serialize a 8-bit signed integer.
     *
     * @param integer $value The value to serialize.
     *
     * @return string The serialized value.
     */
    private function serializeSignedInt8($value)
    {
        if ($value < 0) {
            $value += 0x100;
        }

        return chr($value);
    }

    // /**
    //  * Parse a 8-bit unsigned integer from the head of the buffer.
    //  *
    //  * @return integer
    //  */
    // private function parseUnsignedInt8()
    // {
    //     try {
    //         return ord($this->buffer);
    //     } finally {
    //         $this->buffer = substr($this->buffer, 1);
    //     }
    // } // @codeCoverageIgnore

    // /**
    //  * Parse a 16-bit signed integer from the head of the buffer.
    //  *
    //  * @return integer
    //  */
    // private function parseSignedInt16()
    // {
    //     try {
    //         $result = unpack('n', $this->buffer)[1];

    //         if ($result & 0x8000) {
    //             return $result - 0x10000;
    //         }

    //         return $result;
    //     } finally {
    //         $this->buffer = substr($this->buffer, 2);
    //     }
    // } // @codeCoverageIgnore

    // /**
    //  * Parse a 16-bit unsigned integer from the head of the buffer.
    //  *
    //  * @return integer
    //  */
    // private function parseUnsignedInt16()
    // {
    //     try {
    //         return unpack('n', $this->buffer)[1];
    //     } finally {
    //         $this->buffer = substr($this->buffer, 2);
    //     }
    // } // @codeCoverageIgnore

    /**
     * Serialize a 32-bit signed integer.
     *
     * @param integer $value The value to serialize.
     *
     * @return string The serialized value.
     */
    private function serializeSignedInt32($value)
    {
        if ($value < 0) {
            $value += 0x100000000;
        }

        return pack('N', $value);
    }

    // /**
    //  * Parse a 32-bit unsigned integer from the head of the buffer.
    //  *
    //  * @return integer
    //  */
    // private function parseUnsignedInt32()
    // {
    //     try {
    //         return unpack('N', $this->buffer)[1];
    //     } finally {
    //         $this->buffer = substr($this->buffer, 4);
    //     }
    // } // @codeCoverageIgnore

    /**
     * Serialize a 64-bit signed integer.
     *
     * @param integer $value The value to serialize.
     *
     * @return string The serialized value.
     */
    private function serializeSignedInt64($value)
    {
        return pack('J', $value);
    }

    // /**
    //  * Parse a 64-bit unsigned integer from the head of the buffer.
    //  *
    //  * @return integer|string A string is returned when the value is is outside
    //  *                        the range of PHP's signed integer type.
    //  */
    // private function parseUnsignedInt64()
    // {
    //     try {
    //         $result = unpack('J', $this->buffer)[1];

    //         if ($result < 0) {
    //             return sprintf('%u', $result);
    //         }

    //         return $result;
    //     } finally {
    //         $this->buffer = substr($this->buffer, 8);
    //     }
    // } // @codeCoverageIgnore

    /**
     * Serialize a float (4-byte).
     *
     * @param float $value The value to serialize.
     *
     * @return string The serialized value.
     */
    public function serializeFloat($value)
    {
        if (Endianness::LITTLE) {
            return strrev(pack('f', $value));
        } else {
            return pack('f', $value);
        }
    }

    /**
     * Serialize a double (8-byte).
     *
     * @param float $value The value to serialize.
     *
     * @return string The serialized value.
     */
    public function serializeDouble($value)
    {
        if (Endianness::LITTLE) {
            return strrev(pack('d', $value));
        } else {
            return pack('d', $value);
        }
    }

    // /**
    //  * Parse a double (8-byte) from the head of the buffer.
    //  *
    //  * @return float
    //  */
    // public function parseDouble()
    // {
    //     try {
    //         if (Endianness::LITTLE) {
    //             return unpack('d', strrev(substr($this->buffer, 0, 8)))[1];
    //         } else {
    //             return unpack('d', $this->buffer)[1];
    //         }
    //     } finally {
    //         $this->buffer = substr($this->buffer, 8);
    //     }
    // } // @codeCoverageIgnore
}
