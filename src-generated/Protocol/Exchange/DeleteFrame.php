<?php
namespace Recoil\Amqp\Protocol\Exchange;

use Recoil\Amqp\Protocol\OutgoingFrame;
use Recoil\Amqp\Protocol\OutgoingFrameVisitor;

final class DeleteFrame implements OutgoingFrame
{
    public $channel;
    public $reserved; // short
    public $exchange; // shortstr
    public $ifUnused; // bit
    public $nowait; // bit

    public function acceptOutgoingFrameVisitor(OutgoingFrameVisitor $visitor)
    {
        return $visitor->visitExchangeDeleteFrame($this);
    }
}
