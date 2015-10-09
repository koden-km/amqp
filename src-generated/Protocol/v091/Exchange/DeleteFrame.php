<?php
namespace Recoil\Amqp\Protocol\v091\Exchange;

use Recoil\Amqp\Protocol\FrameVisitor;
use Recoil\Amqp\Protocol\OutgoingFrame;

final class DeleteFrame implements OutgoingFrame
{
    public $channel;
    public $reserved1; // short
    public $exchange; // shortstr
    public $ifUnused; // bit
    public $nowait; // bit

    public function accept(FrameVisitor $visitor)
    {
        return $visitor->visitExchangeDeleteFrame($this);
    }
}
