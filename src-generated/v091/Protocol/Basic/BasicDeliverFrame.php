<?php
namespace Recoil\Amqp\v091\Protocol\Basic;

use Recoil\Amqp\v091\Protocol\IncomingFrame;

final class BasicDeliverFrame implements IncomingFrame
{
    public $frameChannelId;
    public $consumerTag; // shortstr
    public $deliveryTag; // longlong
    public $redelivered; // bit
    public $exchange; // shortstr
    public $routingKey; // shortstr

    public static function create(
        $frameChannelId = 0
      , $consumerTag = null
      , $deliveryTag = null
      , $redelivered = null
      , $exchange = null
      , $routingKey = null
    ) {
        $frame = new self();

        $frame->frameChannelId = $frameChannelId;
        $frame->consumerTag = $consumerTag;
        $frame->deliveryTag = $deliveryTag;
        $frame->redelivered = null === $redelivered ? false : $redelivered;
        $frame->exchange = $exchange;
        $frame->routingKey = $routingKey;

        return $frame;
    }
}
