<?php
namespace Recoil\Amqp\Protocol\Basic;

use Recoil\Amqp\Protocol\OutgoingFrame;
use Recoil\Amqp\Protocol\OutgoingFrameVisitor;

final class CancelFrame implements OutgoingFrame
{
    public $channel;
    public $consumerTag; // shortstr
    public $nowait; // bit

    public function acceptOutgoingFrameVisitor(OutgoingFrameVisitor $visitor)
    {
        return $visitor->visitBasicCancelFrame($this);
    }
}
