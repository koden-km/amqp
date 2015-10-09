<?php
namespace Recoil\Amqp\Protocol\v091\Confirm;

use Recoil\Amqp\Protocol\FrameVisitor;
use Recoil\Amqp\Protocol\IncomingFrame;

final class SelectOkFrame implements IncomingFrame
{
    public $channel;

    public function accept(FrameVisitor $visitor)
    {
        return $visitor->visitConfirmSelectOkFrame($this);
    }
}
