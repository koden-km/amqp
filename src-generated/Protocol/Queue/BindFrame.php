<?php
namespace Recoil\Amqp\Protocol\Queue;

use Recoil\Amqp\Protocol\OutgoingFrame;
use Recoil\Amqp\Protocol\OutgoingFrameVisitor;

final class BindFrame implements OutgoingFrame
{
    public $channel;
    public $reserved; // short
    public $queue; // shortstr
    public $exchange; // shortstr
    public $routingKey; // shortstr
    public $nowait; // bit
    public $arguments; // table

    public function acceptOutgoingFrameVisitor(OutgoingFrameVisitor $visitor)
    {
        return $visitor->visitQueueBindFrame($this);
    }
}
