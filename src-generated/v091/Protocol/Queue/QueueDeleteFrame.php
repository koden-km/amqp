<?php
namespace Recoil\Amqp\v091\Protocol\Queue;

use Recoil\Amqp\v091\Protocol\OutgoingFrame;

final class QueueDeleteFrame implements OutgoingFrame
{
    public $frameChannelId;
    public $reserved1; // short
    public $queue; // shortstr
    public $ifUnused; // bit
    public $ifEmpty; // bit
    public $nowait; // bit

    public static function create(
        $frameChannelId = 0
      , $reserved1 = null
      , $queue = null
      , $ifUnused = null
      , $ifEmpty = null
      , $nowait = null
    ) {
        $frame = new self();

        $frame->frameChannelId = $frameChannelId;
        $frame->reserved1 = null === $reserved1 ? 0 : $reserved1;
        $frame->queue = null === $queue ? '' : $queue;
        $frame->ifUnused = null === $ifUnused ? false : $ifUnused;
        $frame->ifEmpty = null === $ifEmpty ? false : $ifEmpty;
        $frame->nowait = null === $nowait ? false : $nowait;

        return $frame;
    }
}
