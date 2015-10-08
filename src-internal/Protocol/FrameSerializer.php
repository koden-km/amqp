<?php
namespace Recoil\Amqp\Protocol;

use RuntimeException;

final class FrameSerializer implements OutgoingFrameVisitor
{
    /**
     * Serialize a frame, for transmission to the server.
     *
     * @param OutgoingFrame $frame The frame to serialize.
     *
     * @return string The binary serialized frame.
     */
    public function serialize(OutgoingFrame $frame)
    {
        return $frame->acceptOutgoingFrameVisitor($this);
    }

    public function serializeShortString($value)
    {
        return chr(strlen($value)) . $value;
    }

    public function serializeLongString($value)
    {
        return pack("N", strlen($value)) . $value;
    }

    public function serializeCredentials($username, $password)
    {
        return $this->serializeShortString('LOGIN')
             . 'S' . $this->serializeLongString($username)
             . $this->serializeShortString('PASSWORD')
             . 'S' . $this->serializeLongString($password);
    }

    public function serializeTable(array $table)
    {
        $buffer = '';

        foreach ($table as $key => $value) {
            $buffer .= $this->serializeShortString($key);

            if (is_string($value)) {
                // if (strlen($value) <= 0xff) {
                //     $buffer .= 's' . $this->serializeShortString($value);
                // } else {
                    $buffer .= 'S' . $this->serializeLongString($value);
                // }
            }

            // if (is_bool($value)) {
            //     $buffer .= 't' . ord($value);
            // } elseif (is_float($value)) {
            //     $buffer .= 'd' .
            // }
            // } elseif (is_int($value)
        }

        return $this->serializeLongString($buffer);
    }

    private function parseTableValue($type)
    {
        switch ($type) {
            case "t": return $this->parseUnsignedInt8() !== 0;
            case "b": return $this->parseSignedInt8();
            case "B": return $this->parseUnsignedInt8();
            case "U": return $this->parseSignedInt16();
            case "u": return $this->parseUnsignedInt16();
            case "I": return $this->parseSignedInt32();
            case "i": return $this->parseUnsignedInt32();
            case "L": return $this->parseSignedInt64();
            case "l": return $this->parseUnsignedInt64();
            case "f": return $this->parseFloat();
            case "d": return $this->parseDouble();
            case "D": return $this->parseDecimal();
            case "s": return $this->parseShortString();
            case "S": return $this->parseLongString();
            case "A": return $this->parseArray();
            case "T": return $this->parseUnsignedInt64();
            case "F": return $this->parseTable();
            case "V": return null;
        }

        return '<shit>!';
        throw new RuntimeException('Unknown field type: ' . $type . '.');
    }

    /**
     * Consume a 8-bit signed integer from the buffer.
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
     * Consume a 8-bit unsigned integer from the buffer.
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
     * Consume a 16-bit signed integer from the buffer.
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
     * Consume a 16-bit unsigned integer from the buffer.
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
     * Consume a 32-bit signed integer from the buffer.
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
     * Consume a 32-bit unsigned integer from the buffer.
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
     * Consume a 64-bit signed integer from the buffer.
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
     * Consume a 64-bit unsigned integer from the buffer.
     *
     * @return integer|string A string is returned when the value is is outside the range of PHP's signed integer type.
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

    use MethodSerializerTrait;
    // use ContentSerializerTrait;
    // use HeartbatSerializerTrait;
}
