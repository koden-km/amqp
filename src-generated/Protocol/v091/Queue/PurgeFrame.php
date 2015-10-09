<?php
namespace Recoil\Amqp\Protocol\v091\Queue;

use Recoil\Amqp\Protocol\FrameVisitor;
use Recoil\Amqp\Protocol\OutgoingFrame;

final class PurgeFrame implements OutgoingFrame
{
    public $channel;
    public $reserved1; // short
    public $queue; // shortstr
    public $nowait; // bit

    public function accept(FrameVisitor $visitor)
    {
        return $visitor->visitQueuePurgeFrame($this);
    }
}
