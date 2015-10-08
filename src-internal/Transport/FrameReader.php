<?php
namespace Recoil\Amqp\Transport;

final class FrameReader
{
    public function feed($buffer)
    {
        $this->buffer .= $buffer;

        while (strlen($this->buffer) >= $this->waitLength) {
            if (self::HEADER_SIZE === $this->waitLength) {
                $length = substr(
                    $this->buffer,
                    self::HEADER_TYPE_SIZE + self::HEADER_CHANNEL_SIZE,
                    self::HEADER_LENGTH_SIZE
                );

                list(, $length) = unpack('N', $length);
                $this->waitLength = self::HEADER_SIZE + $length + self::END_SIZE;
            } elseif (self::END_VALUE === $this->buffer[$this->waitLength - 1]) {
                $values = unpack('Ctype/nchannel', $this->buffer);

                $frame = new Frame(
                    $values['type'],
                    $values['channel'],
                    substr(
                        $this->buffer,
                        self::HEADER_SIZE,
                        $this->waitLength - self::HEADER_SIZE - self::END_SIZE
                    )
                );

                self::dump($frame);
                yield $frame;

                $this->buffer = substr($this->buffer, $this->waitLength) ?: '';
                $this->waitLength = self::HEADER_SIZE;
            } else {
                throw new RuntimeException('Invalid frame end octet.');
            }
        }
    }

    private function dump(Frame $frame)
    {
        $header = sprintf(
            '>>> frame received [type: %d, channel: %d]',
            $frame->type,
            $frame->channel
        );

        echo $header . PHP_EOL;
        echo PHP_EOL;
        echo self::hex($frame->payload);
        echo PHP_EOL;
    }

    private function hex($buffer, $width = 32)
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

    const HEADER_TYPE_SIZE = 1;
    const HEADER_CHANNEL_SIZE = 2;
    const HEADER_LENGTH_SIZE = 4;
    const HEADER_SIZE = self::HEADER_TYPE_SIZE
                      + self::HEADER_CHANNEL_SIZE
                      + self::HEADER_LENGTH_SIZE;

    const END_SIZE = 1;
    const END_VALUE = "\xce";

    private $buffer = '';
    private $waitLength = self::HEADER_SIZE;
}
