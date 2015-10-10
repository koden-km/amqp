<?php
namespace Recoil\Amqp\v091\Protocol\Confirm;

use Recoil\Amqp\v091\Protocol\OutgoingFrame;
use Recoil\Amqp\v091\Protocol\OutgoingFrameVisitor;

final class ConfirmSelectFrame implements OutgoingFrame
{
    public $channel;
    public $nowait; // bit

    public static function create(
        $channel = 0
      , $nowait = null
    ) {
        $frame = new self();

        $frame->channel = $channel;
        $frame->nowait = null === $nowait ? false : $nowait;

        return $frame;
    }

    public function acceptOutgoing(OutgoingFrameVisitor $visitor)
    {
        return $visitor->visitOutgoingConfirmSelectFrame($this);
    }
}
