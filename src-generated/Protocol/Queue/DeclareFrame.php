<?php
namespace Recoil\Amqp\Protocol\Queue;

use Recoil\Amqp\Protocol\OutgoingFrame;
use Recoil\Amqp\Protocol\OutgoingFrameVisitor;

final class DeclareFrame implements OutgoingFrame
{
    public $channel;
    public $reserved; // short
    public $queue; // shortstr
    public $passive; // bit
    public $durable; // bit
    public $exclusive; // bit
    public $autoDelete; // bit
    public $nowait; // bit
    public $arguments; // table

    public function acceptOutgoingFrameVisitor(OutgoingFrameVisitor $visitor)
    {
        return $visitor->visitQueueDeclareFrame($this);
    }
}
