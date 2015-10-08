<?php
namespace Recoil\Amqp\Protocol\Exchange;

use Recoil\Amqp\Protocol\OutgoingFrame;
use Recoil\Amqp\Protocol\OutgoingFrameVisitor;

final class BindFrame implements OutgoingFrame
{
    public $channel;
    public $reserved; // short
    public $destination; // shortstr
    public $source; // shortstr
    public $routingKey; // shortstr
    public $nowait; // bit
    public $arguments; // table

    public function acceptOutgoingFrameVisitor(OutgoingFrameVisitor $visitor)
    {
        return $visitor->visitExchangeBindFrame($this);
    }
}
