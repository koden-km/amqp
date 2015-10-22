<?php
namespace Recoil\Amqp\v091\Protocol\Connection;

use Recoil\Amqp\v091\Protocol\IncomingFrame;

final class ConnectionSecureFrame implements IncomingFrame
{
    public $frameChannelId;
    public $challenge; // longstr

    public static function create(
        $frameChannelId = 0
      , $challenge = null
    ) {
        $frame = new self();

        $frame->frameChannelId = $frameChannelId;
        $frame->challenge = $challenge;

        return $frame;
    }
}
