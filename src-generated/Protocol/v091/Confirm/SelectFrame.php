<?php
namespace Recoil\Amqp\Protocol\v091\Confirm;

use Recoil\Amqp\Protocol\FrameVisitor;
use Recoil\Amqp\Protocol\OutgoingFrame;

final class SelectFrame implements OutgoingFrame
{
    public $channel;
    public $nowait; // bit

    public function accept(FrameVisitor $visitor)
    {
        return $visitor->visitConfirmSelectFrame($this);
    }
}
