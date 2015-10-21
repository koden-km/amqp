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
    }

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
    }

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
    }

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
    }

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
    }

    /**
     * Parse a 16-bit unsigned integer from the head of the buffer.
     *
     * @return integer
     */
    private function parseUnsignedInt16()
    {
        try {
            return unpack('n', $this->buffer)[1];
        } finally {
            $this->buffer = substr($this->buffer, 2);
        }
    }

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
    }

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
    }

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
    }

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
    }

    /**
     * Parse an AMQP decimal from the head of the buffer.
     *
     * @return float
     */
    public function parseDecimal()
    {
        $scale = $this->parseUnsignedInt8();
        $value = $this->parseUnsignedInt32();

        return $value * pow(10, $scale);
    }

    /**
     * Parse a float (4-byte) from the head of the buffer.
     *
     * @return float
     */
    public function parseFloat()
    {
        if (null === self::$littleEndian) {
            // S = machine order unsigned short, v = little-endian order
            self::$littleEndian = pack('S', 1) === pack('v', 1);
        }

        try {
            if (self::$littleEndian) {
                return unpack('f', strrev(substr($buffer, 0, 4)));
            } else {
                return unpack('f', $this->buffer);
            }
        } finally {
            $this->buffer = substr($this->buffer, 4);
        }
    }

    /**
     * Parse a double (8-byte) from the head of the buffer.
     *
     * @return float
     */
    public function parseDouble()
    {
        if (null === self::$littleEndian) {
            // S = machine order unsigned short, v = little-endian order
            self::$littleEndian = pack('S', 1) === pack('v', 1);
        }

        try {
            if (self::$littleEndian) {
                return unpack('d', strrev(substr($buffer, 0, 8)));
            } else {
                return unpack('d', $this->buffer);
            }
        } finally {
            $this->buffer = substr($this->buffer, 8);
        }
    }

    /**
     * @var boolean True if the current machine uses little-endian byte-order.
     */
    private static $littleEndian;
}
