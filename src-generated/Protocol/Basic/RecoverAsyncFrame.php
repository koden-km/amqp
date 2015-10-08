<?php
namespace Recoil\Amqp\Protocol\Basic;

use Recoil\Amqp\Protocol\OutgoingFrame;
use Recoil\Amqp\Protocol\OutgoingFrameVisitor;

final class RecoverAsyncFrame implements OutgoingFrame
{
    public $channel;
    public $requeue; // bit

    public function acceptOutgoingFrameVisitor(OutgoingFrameVisitor $visitor)
    {
        return $visitor->visitBasicRecoverAsyncFrame($this);
    }
}
