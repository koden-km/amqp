<?php
namespace Recoil\Amqp\Protocol\v091\Basic;

use Recoil\Amqp\Protocol\FrameVisitor;
use Recoil\Amqp\Protocol\IncomingFrame;

final class GetOkFrame implements IncomingFrame
{
    public $channel;
    public $deliveryTag; // longlong
    public $redelivered; // bit
    public $exchange; // shortstr
    public $routingKey; // shortstr
    public $messageCount; // long

    public function accept(FrameVisitor $visitor)
    {
        return $visitor->visitBasicGetOkFrame($this);
    }
}
