<?php
namespace Recoil\Amqp\Protocol\Basic;

use Recoil\Amqp\Protocol\OutgoingFrame;
use Recoil\Amqp\Protocol\OutgoingFrameVisitor;

final class QosFrame implements OutgoingFrame
{
    public $channel;
    public $prefetchSize; // long
    public $prefetchCount; // short
    public $global; // bit

    public function acceptOutgoingFrameVisitor(OutgoingFrameVisitor $visitor)
    {
        return $visitor->visitBasicQosFrame($this);
    }
}
