<?php
namespace Recoil\Amqp\Protocol\v091\Queue;

use Recoil\Amqp\Protocol\FrameVisitor;
use Recoil\Amqp\Protocol\OutgoingFrame;

final class DeleteFrame implements OutgoingFrame
{
    public $channel;
    public $reserved1; // short
    public $queue; // shortstr
    public $ifUnused; // bit
    public $ifEmpty; // bit
    public $nowait; // bit

    public function accept(FrameVisitor $visitor)
    {
        return $visitor->visitQueueDeleteFrame($this);
    }
}
