<?php
namespace Recoil\Amqp\v091\Protocol\Exchange;

use Recoil\Amqp\v091\Protocol\OutgoingFrame;
use Recoil\Amqp\v091\Protocol\OutgoingFrameVisitor;

final class ExchangeDeleteFrame implements OutgoingFrame
{
    public $channel;
    public $reserved1; // short
    public $exchange; // shortstr
    public $ifUnused; // bit
    public $nowait; // bit

    public static function create(
        $channel = 0
      , $reserved1 = null
      , $exchange = null
      , $ifUnused = null
      , $nowait = null
    ) {
        $frame = new self();

        $frame->channel = $channel;
        $frame->reserved1 = null === $reserved1 ? 0 : $reserved1;
        $frame->exchange = $exchange;
        $frame->ifUnused = null === $ifUnused ? false : $ifUnused;
        $frame->nowait = null === $nowait ? false : $nowait;

        return $frame;
    }

    public function acceptOutgoing(OutgoingFrameVisitor $visitor)
    {
        return $visitor->visitOutgoingExchangeDeleteFrame($this);
    }
}
