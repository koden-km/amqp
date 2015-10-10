<?php
namespace Recoil\Amqp\v091\Protocol\Basic;

use Recoil\Amqp\v091\Protocol\IncomingFrame;
use Recoil\Amqp\v091\Protocol\IncomingFrameVisitor;
use Recoil\Amqp\v091\Protocol\OutgoingFrame;
use Recoil\Amqp\v091\Protocol\OutgoingFrameVisitor;

final class BasicAckFrame implements IncomingFrame, OutgoingFrame
{
    public $channel;
    public $deliveryTag; // longlong
    public $multiple; // bit

    public static function create(
        $channel = 0
      , $deliveryTag = null
      , $multiple = null
    ) {
        $frame = new self();

        $frame->channel = $channel;
        $frame->deliveryTag = null === $deliveryTag ? 0 : $deliveryTag;
        $frame->multiple = null === $multiple ? false : $multiple;

        return $frame;
    }

    public function acceptIncoming(IncomingFrameVisitor $visitor)
    {
        return $visitor->visitIncomingBasicAckFrame($this);
    }
    public function acceptOutgoing(OutgoingFrameVisitor $visitor)
    {
        return $visitor->visitOutgoingBasicAckFrame($this);
    }
}
