<?php
namespace Recoil\Amqp\v091\Protocol\Channel;

use Recoil\Amqp\v091\Protocol\IncomingFrame;
use Recoil\Amqp\v091\Protocol\IncomingFrameVisitor;
use Recoil\Amqp\v091\Protocol\OutgoingFrame;
use Recoil\Amqp\v091\Protocol\OutgoingFrameVisitor;

final class ChannelFlowFrame implements IncomingFrame, OutgoingFrame
{
    public $channel;
    public $active; // bit

    public static function create(
        $channel = 0
      , $active = null
    ) {
        $frame = new self();

        $frame->channel = $channel;
        $frame->active = $active;

        return $frame;
    }

    public function acceptIncoming(IncomingFrameVisitor $visitor)
    {
        return $visitor->visitIncomingChannelFlowFrame($this);
    }
    public function acceptOutgoing(OutgoingFrameVisitor $visitor)
    {
        return $visitor->visitOutgoingChannelFlowFrame($this);
    }
}
