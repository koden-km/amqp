<?php

namespace Recoil\Amqp\v091\Protocol;

use Recoil\Amqp\v091\Debug;

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
        if (Debug::ENABLED) {
            Debug::dumpOutgoingFrame($frame);
        }

        return $frame->acceptOutgoing($this);
    }

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

    public function visitOutgoingHeartbeatFrame(HeartbeatFrame $frame)
    {
        if (null === self::$heartbeatBuffer) {
            self::$heartbeatBuffer = chr(Constants::FRAME_HEARTBEAT)
                                   . "\x00\x00" // channel
                                   . "\x00\x00\x00\x00" // size
                                   . chr(Constants::FRAME_END);
        }

        return self::$heartbeatBuffer;
    }

    use MethodSerializerTrait;

    private static $heartbeatBuffer;
}
