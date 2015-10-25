<?php

namespace Recoil\Amqp\v091\Protocol;

use Recoil\Amqp\Connection;

/**
 * A connection to an AMQP server.
 *
 * @codeCoverageIgnore
 */
final class Debug
{
    const ENABLED = false;

    public static function dumpOutgoingFrame(OutgoingFrame $frame)
    {
        if (!self::ENABLED) {
            throw new LogicException('Debug is not enabled, for performance reasons you should check Debug::ENABLED before calling ' . __METHOD__ . '.');
        }

        $name = explode('\\', get_class($frame));
        $name = end($name);

        printf(
            '[%04x] SEND %s' . PHP_EOL,
            $frame->frameChannelId,
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
            $frame->frameChannelId,
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
        unset($properties['channelId']);

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

    private function __construct()
    {
    }
}
