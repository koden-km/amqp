<?php
namespace Recoil\Amqp\v091\Protocol\Basic;

use Recoil\Amqp\v091\Protocol\OutgoingFrame;
use Recoil\Amqp\v091\Protocol\OutgoingFrameVisitor;

final class BasicRejectFrame implements OutgoingFrame
{
    public $channel;
    public $deliveryTag; // longlong
    public $requeue; // bit

    public static function create(
        $channel = 0
      , $deliveryTag = null
      , $requeue = null
    ) {
        $frame = new self();

        $frame->channel = $channel;
        $frame->deliveryTag = $deliveryTag;
        $frame->requeue = null === $requeue ? true : $requeue;

        return $frame;
    }

    public function acceptOutgoing(OutgoingFrameVisitor $visitor)
    {
        return $visitor->visitOutgoingBasicRejectFrame($this);
    }
}
