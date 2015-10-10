<?php
namespace Recoil\Amqp\v091\Protocol\Basic;

use Recoil\Amqp\v091\Protocol\IncomingFrame;
use Recoil\Amqp\v091\Protocol\IncomingFrameVisitor;

final class BasicGetOkFrame implements IncomingFrame
{
    public $channel;
    public $deliveryTag; // longlong
    public $redelivered; // bit
    public $exchange; // shortstr
    public $routingKey; // shortstr
    public $messageCount; // long

    public static function create(
        $channel = 0
      , $deliveryTag = null
      , $redelivered = null
      , $exchange = null
      , $routingKey = null
      , $messageCount = null
    ) {
        $frame = new self();

        $frame->channel = $channel;
        $frame->deliveryTag = $deliveryTag;
        $frame->redelivered = null === $redelivered ? false : $redelivered;
        $frame->exchange = $exchange;
        $frame->routingKey = $routingKey;
        $frame->messageCount = $messageCount;

        return $frame;
    }

    public function acceptIncoming(IncomingFrameVisitor $visitor)
    {
        return $visitor->visitIncomingBasicGetOkFrame($this);
    }
}
