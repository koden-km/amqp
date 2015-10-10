<?php
namespace Recoil\Amqp\v091\Protocol\Basic;

use Recoil\Amqp\v091\Protocol\OutgoingFrame;
use Recoil\Amqp\v091\Protocol\OutgoingFrameVisitor;

final class BasicRecoverAsyncFrame implements OutgoingFrame
{
    public $channel;
    public $requeue; // bit

    public static function create(
        $channel = 0
      , $requeue = null
    ) {
        $frame = new self();

        $frame->channel = $channel;
        $frame->requeue = null === $requeue ? false : $requeue;

        return $frame;
    }

    public function acceptOutgoing(OutgoingFrameVisitor $visitor)
    {
        return $visitor->visitOutgoingBasicRecoverAsyncFrame($this);
    }
}
