<?php
namespace Recoil\Amqp\v091\Protocol\Channel;

use Recoil\Amqp\v091\Protocol\IncomingFrame;
use Recoil\Amqp\v091\Protocol\IncomingFrameVisitor;
use Recoil\Amqp\v091\Protocol\OutgoingFrame;
use Recoil\Amqp\v091\Protocol\OutgoingFrameVisitor;

final class ChannelCloseOkFrame implements IncomingFrame, OutgoingFrame
{
    public $channel;

    public static function create(
        $channel = 0
    ) {
        $frame = new self();

        $frame->channel = $channel;

        return $frame;
    }

    public function acceptIncoming(IncomingFrameVisitor $visitor)
    {
        return $visitor->visitIncomingChannelCloseOkFrame($this);
    }
    public function acceptOutgoing(OutgoingFrameVisitor $visitor)
    {
        return $visitor->visitOutgoingChannelCloseOkFrame($this);
    }
}
