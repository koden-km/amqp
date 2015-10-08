<?php
namespace Recoil\Amqp\Protocol\Confirm;

use Recoil\Amqp\Protocol\OutgoingFrame;
use Recoil\Amqp\Protocol\OutgoingFrameVisitor;

final class SelectFrame implements OutgoingFrame
{
    public $channel;
    public $nowait; // bit

    public function acceptOutgoingFrameVisitor(OutgoingFrameVisitor $visitor)
    {
        return $visitor->visitConfirmSelectFrame($this);
    }
}
