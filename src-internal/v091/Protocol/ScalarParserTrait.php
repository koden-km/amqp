<?php

namespace Recoil\Amqp\v091\Protocol;

/**
 * Parse scalar values from an internal buffer.
 *
 * This trait requires a string property named buffer, which it does NOT declare.
 */
trait ScalarParserTrait
{
    /**
     * Parse an AMQP "short string" from the head of the buffer.
     *
     * The maximum length of a short string is 255 bytes.
     *
     * @return string The UTF-8 string read from the buffer.
     * @todo UTF-8 validation, maybe.
     */
    private function parseShortString()
    {
        $length = ord($this->buffer);

        try {
            return substr($this->buffer, 1, $length);
        } finally {
            $this->buffer = substr($this->buffer, $length + 1);
        }
    } // @codeCoverageIgnore

    /**
     * Parse an AMQP "long string" from the head of the buffer.
     *
     * @return string The UTF-8 string read from the buffer.
     * @todo UTF-8 validation, maybe.
     */
    private function parseLongString()
    {
        list(, $length) = unpack('N', $this->buffer);

        try {
            return substr($this->buffer, 4, $length);
        } finally {
            $this->buffer = substr($this->buffer, $length + 4);
        }
    } // @codeCoverageIgnore

    /**
     * Parse a 8-bit signed integer from the head of the buffer.
     *
     * @return integer
     */
    private function parseSignedInt8()
    {
        try {
            $result = ord($this->buffer);

            if ($result & 0x80) {
                return $result - 0x100;
            }

            return $result;
        } finally {
            $this->buffer = substr($this->buffer, 1);
        }
    } // @codeCoverageIgnore

    /**
     * Parse a 8-bit unsigned integer from the head of the buffer.
     *
     * @return integer
     */
    private function parseUnsignedInt8()
    {
        try {
            return ord($this->buffer);
        } finally {
            $this->buffer = substr($this->buffer, 1);
        }
    } // @codeCoverageIgnore

    /**
     * Parse a 16-bit signed integer from the head of the buffer.
     *
     * @return integer
     */
    private function parseSignedInt16()
    {
        try {
            $result = unpack('n', $this->buffer)[1];

            if ($result & 0x8000) {
                return $result - 0x10000;
            }

            return $result;
        } finally {
            $this->buffer = substr($this->buffer, 2);
        }
    } // @codeCoverageIgnore

    /**
     * Parse a 32-bit signed integer from the head of the buffer.
     *
     * @return integer
     */
    private function parseSignedInt32()
    {
        try {
            $result = unpack('N', $this->buffer)[1];

            if ($result & 0x80000000) {
                return $result - 0x100000000;
            }

            return $result;
        } finally {
            $this->buffer = substr($this->buffer, 4);
        }
    } // @codeCoverageIgnore

    /**
     * Parse a 32-bit unsigned integer from the head of the buffer.
     *
     * @return integer
     */
    private function parseUnsignedInt32()
    {
        try {
            return unpack('N', $this->buffer)[1];
        } finally {
            $this->buffer = substr($this->buffer, 4);
        }
    } // @codeCoverageIgnore

    /**
     * Parse a 64-bit signed integer from the head of the buffer.
     *
     * @return integer
     */
    private function parseSignedInt64()
    {
        try {
            // 'J' is documented as unsigned, but because PHP only has a signed
            // type a signed value is returned anyway ...
            return unpack('J', $this->buffer)[1];
        } finally {
            $this->buffer = substr($this->buffer, 8);
        }
    } // @codeCoverageIgnore

    /**
     * Parse a 64-bit unsigned integer from the head of the buffer.
     *
     * @return integer|string A string is returned when the value is is outside
     *                        the range of PHP's signed integer type.
     */
    private function parseUnsignedInt64()
    {
        try {
            $result = unpack('J', $this->buffer)[1];

            if ($result < 0) {
                return sprintf('%u', $result);
            }

            return $result;
        } finally {
            $this->buffer = substr($this->buffer, 8);
        }
    } // @codeCoverageIgnore

    /**
     * Parse a float (4-byte) from the head of the buffer.
     *
     * @return float
     */
    public function parseFloat()
    {
        try {
            if (Endianness::LITTLE) {
                return unpack('f', strrev(substr($this->buffer, 0, 4)))[1];
            } else {
                return unpack('f', $this->buffer)[1]; // @codeCoverageIgnore
            }
        } finally {
            $this->buffer = substr($this->buffer, 4);
        }
    } // @codeCoverageIgnore

    /**
     * Parse a double (8-byte) from the head of the buffer.
     *
     * @return float
     */
    public function parseDouble()
    {
        try {
            if (Endianness::LITTLE) {
                return unpack('d', strrev(substr($this->buffer, 0, 8)))[1];
            } else {
                return unpack('d', $this->buffer)[1]; // @codeCoverageIgnore
            }
        } finally {
            $this->buffer = substr($this->buffer, 8);
        }
    } // @codeCoverageIgnore
}
