<?php
namespace Recoil\Amqp\Protocol;

use RuntimeException;

trait TableReaderTrait
{
    private function readTable()
    {
        $length = $this->readUnsignedInt32();
        $stopAt = strlen($this->buffer) - $length;

        $table = [];

        while (strlen($this->buffer) > $stopAt) {
            $key = $this->readShortString();
            $type = $this->buffer[0];
            $this->buffer = substr($this->buffer, 1);

            $table[$key] = $this->readTableValue($type);
        }

        return $table;
    }

    private function readTableValue($type)
    {
        switch ($type) {
            case "t": return $this->readUnsignedInt8() !== 0;
            case "b": return $this->readSignedInt8();
            case "B": return $this->readUnsignedInt8();
            case "U": return $this->readSignedInt16();
            case "u": return $this->readUnsignedInt16();
            case "I": return $this->readSignedInt32();
            case "i": return $this->readUnsignedInt32();
            case "L": return $this->readSignedInt64();
            case "l": return $this->readUnsignedInt64();
            case "f": return $this->readFloat();
            case "d": return $this->readDouble();
            case "D": return $this->readDecimal();
            case "s": return $this->readShortString();
            case "S": return $this->readLongString();
            case "A": return $this->readArray();
            case "T": return $this->readUnsignedInt64();
            case "F": return $this->readTable();
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
    private function readSignedInt8()
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
    private function readUnsignedInt8()
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
    private function readSignedInt16()
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
    private function readUnsignedInt16()
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
    private function readSignedInt32()
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
    private function readUnsignedInt32()
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
    private function readSignedInt64()
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
    private function readUnsignedInt64()
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
}
