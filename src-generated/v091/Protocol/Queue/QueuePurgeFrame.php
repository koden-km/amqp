<?php
namespace Recoil\Amqp\v091\Protocol\Queue;

use Recoil\Amqp\v091\Protocol\OutgoingFrame;

final class QueuePurgeFrame implements OutgoingFrame
{
    public $frameChannelId;
    public $reserved1; // short
    public $queue; // shortstr
    public $nowait; // bit

    public static function create(
        $frameChannelId = 0
      , $reserved1 = null
      , $queue = null
      , $nowait = null
    ) {
        $frame = new self();

        $frame->frameChannelId = $frameChannelId;
        $frame->reserved1 = null === $reserved1 ? 0 : $reserved1;
        $frame->queue = null === $queue ? '' : $queue;
        $frame->nowait = null === $nowait ? false : $nowait;

        return $frame;
    }
}
