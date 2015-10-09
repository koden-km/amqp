<?php
namespace Recoil\Amqp\Protocol\v091\Basic;

use Recoil\Amqp\Protocol\FrameVisitor;
use Recoil\Amqp\Protocol\IncomingFrame;
use Recoil\Amqp\Protocol\OutgoingFrame;

final class NackFrame implements IncomingFrame, OutgoingFrame
{
    public $channel;
    public $deliveryTag; // longlong
    public $multiple; // bit
    public $requeue; // bit

    public function accept(FrameVisitor $visitor)
    {
        return $visitor->visitBasicNackFrame($this);
    }
}
