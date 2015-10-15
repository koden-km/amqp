<?php
namespace Recoil\Amqp\v091\Protocol\Queue;

use Recoil\Amqp\v091\Protocol\OutgoingFrame;

final class QueueBindFrame implements OutgoingFrame
{
    public $channel;
    public $reserved1; // short
    public $queue; // shortstr
    public $exchange; // shortstr
    public $routingKey; // shortstr
    public $nowait; // bit
    public $arguments; // table

    public static function create(
        $channel = 0
      , $reserved1 = null
      , $queue = null
      , $exchange = null
      , $routingKey = null
      , $nowait = null
      , $arguments = null
    ) {
        $frame = new self();

        $frame->channel = $channel;
        $frame->reserved1 = null === $reserved1 ? 0 : $reserved1;
        $frame->queue = null === $queue ? '' : $queue;
        $frame->exchange = $exchange;
        $frame->routingKey = null === $routingKey ? '' : $routingKey;
        $frame->nowait = null === $nowait ? false : $nowait;
        $frame->arguments = null === $arguments ? [] : $arguments;

        return $frame;
    }
}
