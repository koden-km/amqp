<?php

namespace Recoil\Amqp\v091\Protocol;

/**
 * Serializes frames to binary data.
 *
 * Most of this class' logic is in {@see FrameSerializerTrait} which is
 * generated by {@see FrameSerializerTraitGenerator}.
 */
final class GeneratedFrameSerializer implements FrameSerializer
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
     * Serialize a data structure as an AMQP table.
     *
     * @param array<string, mixed> $table
     *
     * @return string The serialized table.
     */
    private function serializeTable(array $table)
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

    /**
     * Serialize a heartbeat frame.
     *
     * @param HeartbeatFrame $frame The frame to serialize.
     *
     * @return string The serialized frame.
     */
    private function serializeHeartbeatFrame(HeartbeatFrame $frame)
    {
        // Cache the heartbeat frame buffer, as they can never differ ...
        if (null === self::$heartbeatBuffer) {
            self::$heartbeatBuffer = chr(Constants::FRAME_HEARTBEAT)
                                   . "\x00\x00" // channel
                                   . "\x00\x00\x00\x00" // size
                                   . chr(Constants::FRAME_END);
        }

        return self::$heartbeatBuffer;
    }

    use FrameSerializerTrait;

    /**
     * @var string The buffer for a serialized heartbeat frame.
     */
    private static $heartbeatBuffer;
}