<?php
namespace Recoil\Amqp\Transport;

final class FrameReader
{
    public function feed($buffer)
    {
        $this->buffer .= $buffer;

        while (strlen($this->buffer) >= $this->waitLength) {
            if (null === $this->type) {
                $values = unpack('Ctype/nchannel/Nsize', $this->buffer);
                $this->buffer = substr($this->buffer, self::HEADER_SIZE) ?: '';

                $this->waitLength = $values['size'] + 1;
                $this->type = $values['type'];
                $this->channel = $values['channel'];
            } elseif (self::FRAME_END === $this->buffer[$this->waitLength - 1]) {
                $frame = new Frame(
                    $this->type,
                    $this->channel,
                    substr($this->buffer, 0, $this->waitLength - 1)
                );

                self::dump($frame);

                yield $frame;

                $this->type = null;
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

    const HEADER_SIZE = 7; // 1-byte size + 2-byte channel + 4-byte size
    const FRAME_END = "\xce";

    private $buffer = '';
    private $waitLength = self::HEADER_SIZE;
    private $type;
    private $channel;
}
