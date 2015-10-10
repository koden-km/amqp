<?php
namespace Recoil\Amqp\v091\Protocol\Queue;

use Recoil\Amqp\v091\Protocol\OutgoingFrame;
use Recoil\Amqp\v091\Protocol\OutgoingFrameVisitor;

final class QueuePurgeFrame implements OutgoingFrame
{
    public $channel;
    public $reserved1; // short
    public $queue; // shortstr
    public $nowait; // bit

    public static function create(
        $channel = 0
      , $reserved1 = null
      , $queue = null
      , $nowait = null
    ) {
        $frame = new self();

        $frame->channel = $channel;
        $frame->reserved1 = null === $reserved1 ? 0 : $reserved1;
        $frame->queue = null === $queue ? '' : $queue;
        $frame->nowait = null === $nowait ? false : $nowait;

        return $frame;
    }

    public function acceptOutgoing(OutgoingFrameVisitor $visitor)
    {
        return $visitor->visitOutgoingQueuePurgeFrame($this);
    }
}
