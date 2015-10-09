<?php
namespace Recoil\Amqp\Protocol\v091\Basic;

use Recoil\Amqp\Protocol\FrameVisitor;
use Recoil\Amqp\Protocol\OutgoingFrame;

final class QosFrame implements OutgoingFrame
{
    public $channel;
    public $prefetchSize; // long
    public $prefetchCount; // short
    public $global; // bit

    public function accept(FrameVisitor $visitor)
    {
        return $visitor->visitBasicQosFrame($this);
    }
}
