<?php
namespace Recoil\Amqp\v091\Protocol\Basic;

use Recoil\Amqp\v091\Protocol\IncomingFrame;

final class BasicGetOkFrame implements IncomingFrame
{
    public $frameChannelId;
    public $deliveryTag; // longlong
    public $redelivered; // bit
    public $exchange; // shortstr
    public $routingKey; // shortstr
    public $messageCount; // long

    public static function create(
        $frameChannelId = 0
      , $deliveryTag = null
      , $redelivered = null
      , $exchange = null
      , $routingKey = null
      , $messageCount = null
    ) {
        $frame = new self();

        $frame->frameChannelId = $frameChannelId;
        $frame->deliveryTag = $deliveryTag;
        $frame->redelivered = null === $redelivered ? false : $redelivered;
        $frame->exchange = $exchange;
        $frame->routingKey = $routingKey;
        $frame->messageCount = $messageCount;

        return $frame;
    }
}
