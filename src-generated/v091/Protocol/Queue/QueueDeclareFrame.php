<?php
namespace Recoil\Amqp\v091\Protocol\Queue;

use Recoil\Amqp\v091\Protocol\OutgoingFrame;

final class QueueDeclareFrame implements OutgoingFrame
{
    public $frameChannelId;
    public $reserved1; // short
    public $queue; // shortstr
    public $passive; // bit
    public $durable; // bit
    public $exclusive; // bit
    public $autoDelete; // bit
    public $nowait; // bit
    public $arguments; // table

    public static function create(
        $frameChannelId = 0
      , $reserved1 = null
      , $queue = null
      , $passive = null
      , $durable = null
      , $exclusive = null
      , $autoDelete = null
      , $nowait = null
      , $arguments = null
    ) {
        $frame = new self();

        $frame->frameChannelId = $frameChannelId;
        $frame->reserved1 = null === $reserved1 ? 0 : $reserved1;
        $frame->queue = null === $queue ? '' : $queue;
        $frame->passive = null === $passive ? false : $passive;
        $frame->durable = null === $durable ? false : $durable;
        $frame->exclusive = null === $exclusive ? false : $exclusive;
        $frame->autoDelete = null === $autoDelete ? false : $autoDelete;
        $frame->nowait = null === $nowait ? false : $nowait;
        $frame->arguments = null === $arguments ? [] : $arguments;

        return $frame;
    }
}
