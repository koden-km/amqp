<?php
namespace Recoil\Amqp\Protocol;

use RuntimeException;

final class FrameParser
{
    public function append($buffer)
    {
        $this->buffer .= $buffer;
    }

    /**
     * @return Frame|null
     */
    public function parseFrame()
    {
        // the number of bytes we'd have left over if we read a frame right now ...
        $spareBytes = strlen($this->buffer) - $this->bytesRequiredForNextFrame;

        // definitely not enough bytes for a frame ...
        if ($spareBytes < 0) {
            return null;

        // we've got that header we were waiting on ...
        } elseif ($this->bytesRequiredForNextFrame === self::MINIMUM_FRAME_SIZE) {
            $payloadLength = unpack(
                'N',
                substr(
                    $this->buffer,
                    3, // type(1) + channel(2)
                    4  // size(4)
                )
            )[1];

            $this->bytesRequiredForNextFrame += $payloadLength;

            // but not enough to cover the payload we just found out about ...
            if ($spareBytes < $payloadLength) {
                return null;
            }

            $spareBytes -= $payloadLength;
        }

        // we've got enough bytes for the entire frame, check the end marker ...
        if (AmqpConstants::FRAME_END !== ord($this->buffer[$this->bytesRequiredForNextFrame - 1])) {
            throw new RuntimeException('Invalid frame end marker.');
        }

        // read and discard the header ...
        list($type, $channel, $payloadLength) = array_values(
            unpack('C_1/n_2/N_3', $this->buffer)
        );

        $this->buffer = substr($this->buffer, self::MINIMUM_FRAME_SIZE - 1);

        // read the frame ...
        if (AmqpConstants::FRAME_METHOD === $type) {
            $frame = $this->parseMethodFrame($channel);
        } elseif (AmqpConstants::FRAME_HEADER === $type) {
            $frame = $this->parseContentHeaderFrame($channel);
        } elseif (AmqpConstants::FRAME_BODY === $type) {
            $frame = $this->parseContentBodyFrame($channel);
        } elseif (AmqpConstants::FRAME_HEARTBEAT === $type) {
            $frame = $this->parseHeartbeatFrame($channel);
        } else {
            throw new RuntimeException('Unexpected frame type: ' . $type);
        }

        $frame->channel = $channel;

        // discard end marker ...
        $this->buffer = substr($this->buffer, 1);

        // the frame lied about its payload size ...
        if (strlen($this->buffer) !== $spareBytes) {
            throw new RuntimeException('Frame payload size did not match frame header.');
        }

        // reset the required byte count ...
        $this->bytesRequiredForNextFrame = self::MINIMUM_FRAME_SIZE;

        return $frame;
    }

    private function parseContentHeaderFrame()
    {
    }

    private function parseContentBodyFrame()
    {
    }

    private function parseHeartbeatFrame()
    {
    }

    private function parseShortString()
    {
        $length = ord($this->buffer);

        try {
            return substr($this->buffer, 1, $length);
        } finally {
            $this->buffer = substr($this->buffer, $length + 1);
        }
    }

    private function parseLongString()
    {
        list(, $length) = unpack("N", $this->buffer);

        try {
            return substr($this->buffer, 4, $length);
        } finally {
            $this->buffer = substr($this->buffer, $length + 4);
        }
    }

    private function parseTable()
    {
        $length = $this->parseUnsignedInt32();
        $stopAt = strlen($this->buffer) - $length;

        $table = [];

        while (strlen($this->buffer) > $stopAt) {
            $key = $this->parseShortString();
            $type = $this->buffer[0];
            $this->buffer = substr($this->buffer, 1);

            $table[$key] = $this->parseTableValue($type);
        }

        return $table;
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

    use MethodParserTrait;
    // use ContentParserTrait; ??
    // use HeartbeatParserTrait; ??

    // minimum frame size is 8 == type(1) + channel(2) + size(4) + end(1)
    const MINIMUM_FRAME_SIZE = 8;

    private $buffer = '';
    private $bytesRequiredForNextFrame = self::MINIMUM_FRAME_SIZE;
}
