<?php

namespace Recoil\Amqp\v091\Protocol;

final class FrameSerializer
{
    private function serializeShortString($value)
    {
        return chr(strlen($value)) . $value;
    }

    private function serializeLongString($value)
    {
        return pack('N', strlen($value)) . $value;
    }

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

    private function serializeHeartbeatFrame(HeartbeatFrame $frame)
    {
        if (null === self::$heartbeatBuffer) {
            self::$heartbeatBuffer = chr(Constants::FRAME_HEARTBEAT)
                                   . "\x00\x00" // channel
                                   . "\x00\x00\x00\x00" // size
                                   . chr(Constants::FRAME_END);
        }

        return self::$heartbeatBuffer;
    }

    use FrameSerializerTrait;

    private static $heartbeatBuffer;
}
