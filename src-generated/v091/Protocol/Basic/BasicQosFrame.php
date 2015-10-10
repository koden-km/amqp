<?php
namespace Recoil\Amqp\v091\Protocol\Basic;

use Recoil\Amqp\v091\Protocol\OutgoingFrame;
use Recoil\Amqp\v091\Protocol\OutgoingFrameVisitor;

final class BasicQosFrame implements OutgoingFrame
{
    public $channel;
    public $prefetchSize; // long
    public $prefetchCount; // short
    public $global; // bit

    public static function create(
        $channel = 0
      , $prefetchSize = null
      , $prefetchCount = null
      , $global = null
    ) {
        $frame = new self();

        $frame->channel = $channel;
        $frame->prefetchSize = null === $prefetchSize ? 0 : $prefetchSize;
        $frame->prefetchCount = null === $prefetchCount ? 0 : $prefetchCount;
        $frame->global = null === $global ? false : $global;

        return $frame;
    }

    public function acceptOutgoing(OutgoingFrameVisitor $visitor)
    {
        return $visitor->visitOutgoingBasicQosFrame($this);
    }
}
