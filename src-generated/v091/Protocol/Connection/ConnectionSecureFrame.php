<?php
namespace Recoil\Amqp\v091\Protocol\Connection;

use Recoil\Amqp\v091\Protocol\IncomingFrame;

final class ConnectionSecureFrame implements IncomingFrame
{
    public $channel;
    public $challenge; // longstr

    public static function create(
        $channel = 0
      , $challenge = null
    ) {
        $frame = new self();

        $frame->channel = $channel;
        $frame->challenge = $challenge;

        return $frame;
    }
}
