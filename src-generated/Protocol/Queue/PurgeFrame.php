<?php
namespace Recoil\Amqp\Protocol\Queue;

use Recoil\Amqp\Protocol\OutgoingFrame;
use Recoil\Amqp\Protocol\OutgoingFrameVisitor;

final class PurgeFrame implements OutgoingFrame
{
    public $channel;
    public $reserved; // short
    public $queue; // shortstr
    public $nowait; // bit

    public function acceptOutgoingFrameVisitor(OutgoingFrameVisitor $visitor)
    {
        return $visitor->visitQueuePurgeFrame($this);
    }
}
