<?php
namespace Recoil\Amqp\Protocol\Basic;

use Recoil\Amqp\Protocol\OutgoingFrame;
use Recoil\Amqp\Protocol\OutgoingFrameVisitor;

final class ConsumeFrame implements OutgoingFrame
{
    public $channel;
    public $reserved; // short
    public $queue; // shortstr
    public $consumerTag; // shortstr
    public $noLocal; // bit
    public $noAck; // bit
    public $exclusive; // bit
    public $nowait; // bit
    public $arguments; // table

    public function acceptOutgoingFrameVisitor(OutgoingFrameVisitor $visitor)
    {
        return $visitor->visitBasicConsumeFrame($this);
    }
}
