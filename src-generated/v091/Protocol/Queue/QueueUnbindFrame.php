<?php
namespace Recoil\Amqp\v091\Protocol\Queue;

use Recoil\Amqp\v091\Protocol\OutgoingFrame;

final class QueueUnbindFrame implements OutgoingFrame
{
    public $frameChannelId;
    public $reserved1; // short
    public $queue; // shortstr
    public $exchange; // shortstr
    public $routingKey; // shortstr
    public $arguments; // table

    public static function create(
        $frameChannelId = 0
      , $reserved1 = null
      , $queue = null
      , $exchange = null
      , $routingKey = null
      , $arguments = null
    ) {
        $frame = new self();

        $frame->frameChannelId = $frameChannelId;
        $frame->reserved1 = null === $reserved1 ? 0 : $reserved1;
        $frame->queue = null === $queue ? '' : $queue;
        $frame->exchange = $exchange;
        $frame->routingKey = null === $routingKey ? '' : $routingKey;
        $frame->arguments = null === $arguments ? [] : $arguments;

        return $frame;
    }
}
