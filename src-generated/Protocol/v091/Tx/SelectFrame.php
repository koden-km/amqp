<?php
namespace Recoil\Amqp\Protocol\v091\Tx;

use Recoil\Amqp\Protocol\FrameVisitor;
use Recoil\Amqp\Protocol\OutgoingFrame;

final class SelectFrame implements OutgoingFrame
{
    public $channel;

    public function accept(FrameVisitor $visitor)
    {
        return $visitor->visitTxSelectFrame($this);
    }
}
