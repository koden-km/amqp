<?php
namespace Recoil\Amqp\v091\Protocol\Queue;

use Recoil\Amqp\v091\Protocol\OutgoingFrame;
use Recoil\Amqp\v091\Protocol\OutgoingFrameVisitor;

final class QueueDeleteFrame implements OutgoingFrame
{
    public $channel;
    public $reserved1; // short
    public $queue; // shortstr
    public $ifUnused; // bit
    public $ifEmpty; // bit
    public $nowait; // bit

    public static function create(
        $channel = 0
      , $reserved1 = null
      , $queue = null
      , $ifUnused = null
      , $ifEmpty = null
      , $nowait = null
    ) {
        $frame = new self();

        $frame->channel = $channel;
        $frame->reserved1 = null === $reserved1 ? 0 : $reserved1;
        $frame->queue = null === $queue ? '' : $queue;
        $frame->ifUnused = null === $ifUnused ? false : $ifUnused;
        $frame->ifEmpty = null === $ifEmpty ? false : $ifEmpty;
        $frame->nowait = null === $nowait ? false : $nowait;

        return $frame;
    }

    public function acceptOutgoing(OutgoingFrameVisitor $visitor)
    {
        return $visitor->visitOutgoingQueueDeleteFrame($this);
    }
}
