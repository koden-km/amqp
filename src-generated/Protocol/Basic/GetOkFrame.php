<?php
namespace Recoil\Amqp\Protocol\Basic;

use Recoil\Amqp\Protocol\IncomingFrame;
use Recoil\Amqp\Protocol\IncomingFrameVisitor;

final class GetOkFrame implements IncomingFrame
{
    public $channel;
    public $deliveryTag; // longlong
    public $redelivered; // bit
    public $exchange; // shortstr
    public $routingKey; // shortstr
    public $messageCount; // long

    public function acceptIncomingFrameVisitor(IncomingFrameVisitor $visitor)
    {
        return $visitor->visitBasicGetOkFrame($this);
    }
}
