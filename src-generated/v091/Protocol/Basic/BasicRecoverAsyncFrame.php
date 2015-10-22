<?php
namespace Recoil\Amqp\v091\Protocol\Basic;

use Recoil\Amqp\v091\Protocol\OutgoingFrame;

final class BasicRecoverAsyncFrame implements OutgoingFrame
{
    public $frameChannelId;
    public $requeue; // bit

    public static function create(
        $frameChannelId = 0
      , $requeue = null
    ) {
        $frame = new self();

        $frame->frameChannelId = $frameChannelId;
        $frame->requeue = null === $requeue ? false : $requeue;

        return $frame;
    }
}
