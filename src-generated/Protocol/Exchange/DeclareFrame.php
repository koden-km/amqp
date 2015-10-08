<?php
namespace Recoil\Amqp\Protocol\Exchange;

use Recoil\Amqp\Protocol\OutgoingFrame;
use Recoil\Amqp\Protocol\OutgoingFrameVisitor;

final class DeclareFrame implements OutgoingFrame
{
    public $channel;
    public $reserved; // short
    public $exchange; // shortstr
    public $type; // shortstr
    public $passive; // bit
    public $durable; // bit
    public $autoDelete; // bit
    public $internal; // bit
    public $nowait; // bit
    public $arguments; // table

    public function acceptOutgoingFrameVisitor(OutgoingFrameVisitor $visitor)
    {
        return $visitor->visitExchangeDeclareFrame($this);
    }
}
