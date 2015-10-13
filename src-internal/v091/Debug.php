<?php

namespace Recoil\Amqp\v091;

use Recoil\Amqp\Connection;
use Recoil\Amqp\v091\Protocol\IncomingFrame;
use Recoil\Amqp\v091\Protocol\OutgoingFrame;

/**
 * A connection to an AMQP server.
 */
final class Debug
{
    const ENABLED = true;

    public static function dumpOutgoingFrame(OutgoingFrame $frame)
    {
        if (!self::ENABLED) {
            throw new LogicException('Debug is not enabled, for performance reasons you should check Debug::ENABLED before calling ' . __METHOD__ . '.');
        }

        $name = explode('\\', get_class($frame));
        $name = end($name);

        printf(
            '[%04x] SEND %s' . PHP_EOL,
            $frame->channel,
            $name
        );

        echo PHP_EOL;

        self::dumpFrameProperties($frame);
    }

    public static function dumpIncomingFrame(IncomingFrame $frame)
    {
        if (!self::ENABLED) {
            throw new LogicException('Debug is not enabled, for performance reasons you should check Debug::ENABLED before calling ' . __METHOD__ . '.');
        }

        $name = explode('\\', get_class($frame));
        $name = end($name);

        printf(
            '[%04x] RECV %s' . PHP_EOL,
            $frame->channel,
            $name
        );

        echo PHP_EOL;

        self::dumpFrameProperties($frame);
    }

    public static function dumpFrameProperties($frame)
    {
        if (!self::ENABLED) {
            throw new LogicException('Debug is not enabled, for performance reasons you should check Debug::ENABLED before calling ' . __METHOD__ . '.');
        }

        $properties = get_object_vars($frame);
        unset($properties['channel']);

        if (!$properties) {
            return;
        }

        $length = max(array_map('strlen', array_keys($properties)));

        foreach ($properties as $key => $value) {
            $value = json_encode($value, JSON_PRETTY_PRINT);

            if (false !== strpos($value, PHP_EOL)) {
                $value = str_replace(
                    PHP_EOL,
                    PHP_EOL . '  ' . str_repeat(' ', $length) . ': ',
                    $value
                );
            }
            printf(
                '  %' . $length . 's: %s' . PHP_EOL,
                $key,
                $value
            );
        }

        echo PHP_EOL;
    }

    public static function dumpHex($buffer, $width = 32)
    {
        if (!self::ENABLED) {
            throw new LogicException('Debug is not enabled, for performance reasons you should check Debug::ENABLED before calling ' . __METHOD__ . '.');
        }

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

        echo $output;
    }

    private function __construct()
    {
    }
}
