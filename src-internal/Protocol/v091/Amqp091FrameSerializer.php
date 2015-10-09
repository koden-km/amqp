<?php
namespace Recoil\Amqp\Protocol\v091;

use Recoil\Amqp\Protocol\FrameSerializer;
use Recoil\Amqp\Protocol\OutgoingFrame;

final class Amqp091FrameSerializer implements FrameSerializer, OutgoingFrameVisitor
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
        return $frame->accept($this);
    }

    /**
     * Serialize a username and password suitable for use in the "response"
     * argument of a Start-Ok message when using AMQPLAIN authentication.
     *
     * @param string $username
     * @param string $password
     *
     * @return string The binary serialized frame.
     */
    public function serializePlainCredentials($username, $password)
    {
        return $this->serializeShortString('LOGIN')
             . 'S' . $this->serializeLongString($username)
             . $this->serializeShortString('PASSWORD')
             . 'S' . $this->serializeLongString($password);
    }

    private function serializeShortString($value)
    {
        return chr(strlen($value)) . $value;
    }

    private function serializeLongString($value)
    {
        return pack("N", strlen($value)) . $value;
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

    use MethodSerializerTrait;
}
