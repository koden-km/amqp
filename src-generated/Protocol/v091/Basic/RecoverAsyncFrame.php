<?php
namespace Recoil\Amqp\Protocol\v091\Basic;

use Recoil\Amqp\Protocol\FrameVisitor;
use Recoil\Amqp\Protocol\OutgoingFrame;

final class RecoverAsyncFrame implements OutgoingFrame
{
    public $channel;
    public $requeue; // bit

    public function accept(FrameVisitor $visitor)
    {
        return $visitor->visitBasicRecoverAsyncFrame($this);
    }
}
