<?php
namespace Recoil\Amqp\v091\Protocol\Exchange;

use Recoil\Amqp\v091\Protocol\OutgoingFrame;
use Recoil\Amqp\v091\Protocol\OutgoingFrameVisitor;

final class ExchangeUnbindFrame implements OutgoingFrame
{
    public $channel;
    public $reserved1; // short
    public $destination; // shortstr
    public $source; // shortstr
    public $routingKey; // shortstr
    public $nowait; // bit
    public $arguments; // table

    public static function create(
        $channel = 0
      , $reserved1 = null
      , $destination = null
      , $source = null
      , $routingKey = null
      , $nowait = null
      , $arguments = null
    ) {
        $frame = new self();

        $frame->channel = $channel;
        $frame->reserved1 = null === $reserved1 ? 0 : $reserved1;
        $frame->destination = $destination;
        $frame->source = $source;
        $frame->routingKey = null === $routingKey ? '' : $routingKey;
        $frame->nowait = null === $nowait ? false : $nowait;
        $frame->arguments = null === $arguments ? [] : $arguments;

        return $frame;
    }

    public function acceptOutgoing(OutgoingFrameVisitor $visitor)
    {
        return $visitor->visitOutgoingExchangeUnbindFrame($this);
    }
}
