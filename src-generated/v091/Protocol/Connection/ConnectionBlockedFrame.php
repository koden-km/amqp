<?php
namespace Recoil\Amqp\v091\Protocol\Connection;

use Recoil\Amqp\v091\Protocol\IncomingFrame;

final class ConnectionBlockedFrame implements IncomingFrame
{
    public $channel;
    public $reason; // shortstr

    public static function create(
        $channel = 0
      , $reason = null
    ) {
        $frame = new self();

        $frame->channel = $channel;
        $frame->reason = null === $reason ? '' : $reason;

        return $frame;
    }
}
