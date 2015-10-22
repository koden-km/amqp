<?php
namespace Recoil\Amqp\v091\Protocol\Exchange;

use Recoil\Amqp\v091\Protocol\OutgoingFrame;

final class ExchangeBindFrame implements OutgoingFrame
{
    public $frameChannelId;
    public $reserved1; // short
    public $destination; // shortstr
    public $source; // shortstr
    public $routingKey; // shortstr
    public $nowait; // bit
    public $arguments; // table

    public static function create(
        $frameChannelId = 0
      , $reserved1 = null
      , $destination = null
      , $source = null
      , $routingKey = null
      , $nowait = null
      , $arguments = null
    ) {
        $frame = new self();

        $frame->frameChannelId = $frameChannelId;
        $frame->reserved1 = null === $reserved1 ? 0 : $reserved1;
        $frame->destination = $destination;
        $frame->source = $source;
        $frame->routingKey = null === $routingKey ? '' : $routingKey;
        $frame->nowait = null === $nowait ? false : $nowait;
        $frame->arguments = null === $arguments ? [] : $arguments;

        return $frame;
    }
}
