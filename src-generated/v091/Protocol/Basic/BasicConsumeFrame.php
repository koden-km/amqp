<?php
namespace Recoil\Amqp\v091\Protocol\Basic;

use Recoil\Amqp\v091\Protocol\OutgoingFrame;

final class BasicConsumeFrame implements OutgoingFrame
{
    public $frameChannelId;
    public $reserved1; // short
    public $queue; // shortstr
    public $consumerTag; // shortstr
    public $noLocal; // bit
    public $noAck; // bit
    public $exclusive; // bit
    public $nowait; // bit
    public $arguments; // table

    public static function create(
        $frameChannelId = 0
      , $reserved1 = null
      , $queue = null
      , $consumerTag = null
      , $noLocal = null
      , $noAck = null
      , $exclusive = null
      , $nowait = null
      , $arguments = null
    ) {
        $frame = new self();

        $frame->frameChannelId = $frameChannelId;
        $frame->reserved1 = null === $reserved1 ? 0 : $reserved1;
        $frame->queue = null === $queue ? '' : $queue;
        $frame->consumerTag = null === $consumerTag ? '' : $consumerTag;
        $frame->noLocal = null === $noLocal ? false : $noLocal;
        $frame->noAck = null === $noAck ? false : $noAck;
        $frame->exclusive = null === $exclusive ? false : $exclusive;
        $frame->nowait = null === $nowait ? false : $nowait;
        $frame->arguments = null === $arguments ? [] : $arguments;

        return $frame;
    }
}
