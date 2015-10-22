<?php
namespace Recoil\Amqp\v091\Protocol\Connection;

use Recoil\Amqp\v091\Protocol\IncomingFrame;

final class ConnectionBlockedFrame implements IncomingFrame
{
    public $frameChannelId;
    public $reason; // shortstr

    public static function create(
        $frameChannelId = 0
      , $reason = null
    ) {
        $frame = new self();

        $frame->frameChannelId = $frameChannelId;
        $frame->reason = null === $reason ? '' : $reason;

        return $frame;
    }
}
