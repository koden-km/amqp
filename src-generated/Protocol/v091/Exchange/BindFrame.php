<?php
namespace Recoil\Amqp\Protocol\v091\Exchange;

use Recoil\Amqp\Protocol\FrameVisitor;
use Recoil\Amqp\Protocol\OutgoingFrame;

final class BindFrame implements OutgoingFrame
{
    public $channel;
    public $reserved1; // short
    public $destination; // shortstr
    public $source; // shortstr
    public $routingKey; // shortstr
    public $nowait; // bit
    public $arguments; // table

    public function accept(FrameVisitor $visitor)
    {
        return $visitor->visitExchangeBindFrame($this);
    }
}
