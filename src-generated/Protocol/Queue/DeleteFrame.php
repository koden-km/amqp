<?php
namespace Recoil\Amqp\Protocol\Queue;

use Recoil\Amqp\Protocol\OutgoingFrame;
use Recoil\Amqp\Protocol\OutgoingFrameVisitor;

final class DeleteFrame implements OutgoingFrame
{
    public $channel;
    public $reserved; // short
    public $queue; // shortstr
    public $ifUnused; // bit
    public $ifEmpty; // bit
    public $nowait; // bit

    public function acceptOutgoingFrameVisitor(OutgoingFrameVisitor $visitor)
    {
        return $visitor->visitQueueDeleteFrame($this);
    }
}
