<?php
namespace Recoil\Amqp\Protocol;

use RuntimeException;

final class FrameReader
{
    public function append($buffer)
    {
        $this->buffer .= $buffer;
    }

    /**
     * @return Frame|null
     */
    public function readFrame()
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
            $frame = $this->readMethodFrame($channel);
        } elseif (AmqpConstants::FRAME_HEADER === $type) {
            $frame = $this->readContentHeaderFrame($channel);
        } elseif (AmqpConstants::FRAME_BODY === $type) {
            $frame = $this->readContentBodyFrame($channel);
        } elseif (AmqpConstants::FRAME_HEARTBEAT === $type) {
            $frame = $this->readHeartbeatFrame($channel);
        } else {
            throw new RuntimeException('Unexpected frame type: ' . $type);
        }

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

    private function readContentHeaderFrame($channel)
    {
    }

    private function readContentBodyFrame($channel)
    {
    }

    private function readHeartbeatFrame($channel)
    {
    }

    private static function hex($buffer, $width = 32)
    {
        static $from = '';
        static $to = '';
        static $pad = '.'; # padding for non-visible characters

        if ($from === '') {
            for ($i = 0; $i <= 0xff; ++$i) {
                $from .= chr($i);
                $to .= ($i >= 0x20 && $i <= 0x7e) ? chr($i) : $pad;
            }
        }

        $hex = str_split(
            bin2hex($buffer),
            $width * 2
        );

        $chars = str_split(
            strtr($buffer, $from, $to),
            $width
        );

        $offset = 0;
        $output = '';

        foreach ($hex as $i => $line) {
            $output .= sprintf(
                '%6d : %-' . ($width * 3 - 1) . 's [%s]' . PHP_EOL,
                $offset,
                implode(' ', str_split($line, 2)),
                $chars[$i]
            );

            $offset += $width;
        }

        return $output;
    }

    use StringReaderTrait;
    use TableReaderTrait;

    // generated traits ...
    use MethodReaderTrait;
    // use ContentReaderTrait; ??
    // use HeartbeatReaderTrait; ??

    // minimum frame size is 8 == type(1) + channel(2) + size(4) + end(1)
    const MINIMUM_FRAME_SIZE = 8;

    private $buffer = '';
    private $bytesRequiredForNextFrame = self::MINIMUM_FRAME_SIZE;
}
