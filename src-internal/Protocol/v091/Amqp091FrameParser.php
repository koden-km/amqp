<?php
namespace Recoil\Amqp\Protocol\v091;

use Recoil\Amqp\Protocol\FrameParser;
use Recoil\Amqp\Protocol\ProtocolException;

/**
 * Produces Frame objects from binary data.
 */
class Amqp091FrameParser implements FrameParser
{
    public function __construct()
    {
        $this->requiredBytes = self::MINIMUM_FRAME_SIZE;
        $this->buffer = '';
    }

    /**
     * Retrieve the next frame from the internal buffer.
     *
     * @return mixed<Frame>      The parsed frame, or null if there is not enough data to produce a frame.
     * @throws ProtocolException if the buffer is malformed.
     */
    public function feed($buffer)
    {
        $this->buffer .= $buffer;

        while (true) {
            $availableBytes = strlen($this->buffer);

            // not enough bytes for a frame ...
            if ($availableBytes < $this->requiredBytes) {
                return;

            // we're still looking for the header ...
            } elseif ($this->requiredBytes === self::MINIMUM_FRAME_SIZE) {
                // now that we know the payload size we can add that to the number
                // of required bytes ...
                $this->requiredBytes += unpack(
                    'N',
                    substr(
                        $this->buffer,
                        self::HEADER_TYPE_SIZE + self::HEADER_CHANNEL_SIZE,
                        self::HEADER_PAYLOAD_LENGTH_SIZE
                    )
                )[1];

                // taking the payload into account we don't have enough bytes
                // for the frame ...
                if ($availableBytes < $this->requiredBytes) {
                    return;
                }
            }

            // we've got enough bytes, check that the last byte matches the end
            // marker ...
            if (Constants::FRAME_END !== ord($this->buffer[$this->requiredBytes - 1])) {
                throw ProtocolException::create(
                    sprintf(
                        'Frame end marker (0x%02x) is invalid.',
                        ord($this->buffer[$this->requiredBytes - 1])
                    )
                );
            }

            // read the (t)ype and (c)hannel then discard the header ...
            $fields = unpack('Ct/nc', $this->buffer);
            $this->buffer = substr($this->buffer, self::HEADER_SIZE);

            $type = $fields['t'];

            // read the frame ...
            if (Constants::FRAME_METHOD === $type) {
                $frame = $this->parseMethodFrame();
            } elseif (Constants::FRAME_HEADER === $type) {
                $frame = $this->parseContentHeaderFrame();
            } elseif (Constants::FRAME_BODY === $type) {
                $frame = $this->parseContentBodyFrame();
            } elseif (Constants::FRAME_HEARTBEAT === $type) {
                if (self::MINIMUM_FRAME_SIZE !== $this->requiredBytes) {
                    throw ProtocolException::create(
                        sprintf(
                            'Received heartbeat frame with non-zero (%d) payload size.',
                            $this->requiredBytes - self::MINIMUM_FRAME_SIZE
                        )
                    );
                }
                $frame = new HeartbeatFrame();
            } else {
                throw ProtocolException::create('Received frame of unknown type.');
            }

            $this->buffer = substr($this->buffer, 1);

            $consumedBytes = $availableBytes - strlen($this->buffer);

            // the frame lied about its payload size ...
            if ($consumedBytes !== $this->requiredBytes) {
                throw ProtocolException::create(
                    sprintf(
                        'Mismatch between frame size (%s) and consumed bytes (%s).',
                        $this->requiredBytes,
                        $consumedBytes
                    )
                );
            }

            $this->requiredBytes = self::MINIMUM_FRAME_SIZE;

            $frame->channel = $fields['c'];

            yield $frame;
        }
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
     * Parser a 8-bit signed integer from the buffer.
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
     * Parser a 8-bit unsigned integer from the buffer.
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
     * Parser a 16-bit signed integer from the buffer.
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
     * Parser a 16-bit unsigned integer from the buffer.
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
     * Parser a 32-bit signed integer from the buffer.
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
     * Parser a 32-bit unsigned integer from the buffer.
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
     * Parser a 64-bit signed integer from the buffer.
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
     * Parser a 64-bit unsigned integer from the buffer.
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

    // private function dump($buffer, $width = 32)
    // {
    //     static $from = '';
    //     static $to = '';
    //     static $pad = '.'; # padding for non-visible characters

    //     if ($from === '') {
    //         for ($i = 0; $i <= 0xff; ++$i) {
    //             $from .= chr($i);
    //             $to .= ($i >= 0x20 && $i <= 0x7e) ? chr($i) : $pad;
    //         }
    //     }

    //     $hex = str_split(
    //         bin2hex($buffer),
    //         $width * 2
    //     );

    //     $chars = str_split(
    //         strtr($buffer, $from, $to),
    //         $width
    //     );

    //     $offset = 0;
    //     $output = '';

    //     foreach ($hex as $i => $line) {
    //         $output .= sprintf(
    //             '%6d : %-' . ($width * 3 - 1) . 's [%s]' . PHP_EOL,
    //             $offset,
    //             implode(' ', str_split($line, 2)),
    //             $chars[$i]
    //         );

    //         $offset += $width;
    //     }

    //     echo PHP_EOL;
    //     echo $output;
    //     echo PHP_EOL;
    // }

    use FrameParserMethodTrait;

    // the size of each portion of the header ...
    const HEADER_TYPE_SIZE           = 1; // header field "frame type" - unsigned octet
    const HEADER_CHANNEL_SIZE        = 2; // header field "channel id" - unsigned short
    const HEADER_PAYLOAD_LENGTH_SIZE = 4; // header field "payload length" - unsigned long

    // the total header size ...
    const HEADER_SIZE = self::HEADER_TYPE_SIZE
                      + self::HEADER_CHANNEL_SIZE
                      + self::HEADER_PAYLOAD_LENGTH_SIZE;

    // minimum size of a valid frame (header + end with no payload) ...
    const MINIMUM_FRAME_SIZE = self::HEADER_SIZE + 1; // end marker is always 1 byte

    private $requiredBytes;
    private $buffer;
}
