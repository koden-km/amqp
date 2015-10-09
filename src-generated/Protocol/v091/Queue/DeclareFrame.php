<?php
namespace Recoil\Amqp\Protocol\v091\Queue;

use Recoil\Amqp\Protocol\FrameVisitor;
use Recoil\Amqp\Protocol\OutgoingFrame;

final class DeclareFrame implements OutgoingFrame
{
    public $channel;
    public $reserved1; // short
    public $queue; // shortstr
    public $passive; // bit
    public $durable; // bit
    public $exclusive; // bit
    public $autoDelete; // bit
    public $nowait; // bit
    public $arguments; // table

    public function accept(FrameVisitor $visitor)
    {
        return $visitor->visitQueueDeclareFrame($this);
    }
}
