<?php
namespace Recoil\Amqp\Protocol\v091\Basic;

use Recoil\Amqp\Protocol\FrameVisitor;
use Recoil\Amqp\Protocol\IncomingFrame;

final class DeliverFrame implements IncomingFrame
{
    public $channel;
    public $consumerTag; // shortstr
    public $deliveryTag; // longlong
    public $redelivered; // bit
    public $exchange; // shortstr
    public $routingKey; // shortstr

    public function accept(FrameVisitor $visitor)
    {
        return $visitor->visitBasicDeliverFrame($this);
    }
}
