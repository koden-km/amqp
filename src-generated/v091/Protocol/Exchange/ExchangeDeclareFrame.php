<?php
namespace Recoil\Amqp\v091\Protocol\Exchange;

use Recoil\Amqp\v091\Protocol\OutgoingFrame;

final class ExchangeDeclareFrame implements OutgoingFrame
{
    public $frameChannelId;
    public $reserved1; // short
    public $exchange; // shortstr
    public $type; // shortstr
    public $passive; // bit
    public $durable; // bit
    public $autoDelete; // bit
    public $internal; // bit
    public $nowait; // bit
    public $arguments; // table

    public static function create(
        $frameChannelId = 0
      , $reserved1 = null
      , $exchange = null
      , $type = null
      , $passive = null
      , $durable = null
      , $autoDelete = null
      , $internal = null
      , $nowait = null
      , $arguments = null
    ) {
        $frame = new self();

        $frame->frameChannelId = $frameChannelId;
        $frame->reserved1 = null === $reserved1 ? 0 : $reserved1;
        $frame->exchange = $exchange;
        $frame->type = null === $type ? 'direct' : $type;
        $frame->passive = null === $passive ? false : $passive;
        $frame->durable = null === $durable ? false : $durable;
        $frame->autoDelete = null === $autoDelete ? false : $autoDelete;
        $frame->internal = null === $internal ? false : $internal;
        $frame->nowait = null === $nowait ? false : $nowait;
        $frame->arguments = null === $arguments ? [] : $arguments;

        return $frame;
    }
}
