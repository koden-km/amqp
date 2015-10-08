<?php
namespace Recoil\Amqp\Protocol\Basic;

use Recoil\Amqp\Protocol\OutgoingFrame;
use Recoil\Amqp\Protocol\OutgoingFrameVisitor;

final class RejectFrame implements OutgoingFrame
{
    public $channel;
    public $deliveryTag; // longlong
    public $requeue; // bit

    public function acceptOutgoingFrameVisitor(OutgoingFrameVisitor $visitor)
    {
        return $visitor->visitBasicRejectFrame($this);
    }
}
