<?php
namespace Recoil\Amqp\Protocol\Channel;

use Recoil\Amqp\Protocol\IncomingFrame;
use Recoil\Amqp\Protocol\IncomingFrameVisitor;
use Recoil\Amqp\Protocol\OutgoingFrame;
use Recoil\Amqp\Protocol\OutgoingFrameVisitor;

final class FlowOkFrame implements IncomingFrame, OutgoingFrame
{
    public $channel;
    public $active; // bit

    public function acceptIncomingFrameVisitor(IncomingFrameVisitor $visitor)
    {
        return $visitor->visitChannelFlowOkFrame($this);
    }

    public function acceptOutgoingFrameVisitor(OutgoingFrameVisitor $visitor)
    {
        return $visitor->visitChannelFlowOkFrame($this);
    }
}
