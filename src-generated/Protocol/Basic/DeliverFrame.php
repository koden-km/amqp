<?php
namespace Recoil\Amqp\Protocol\Basic;

use Recoil\Amqp\Protocol\IncomingFrame;
use Recoil\Amqp\Protocol\IncomingFrameVisitor;

final class DeliverFrame implements IncomingFrame
{
    public $channel;
    public $consumerTag; // shortstr
    public $deliveryTag; // longlong
    public $redelivered; // bit
    public $exchange; // shortstr
    public $routingKey; // shortstr

    public function acceptIncomingFrameVisitor(IncomingFrameVisitor $visitor)
    {
        return $visitor->visitBasicDeliverFrame($this);
    }
}
