<?php
namespace Recoil\Amqp\Protocol\v091\Channel;

use Recoil\Amqp\Protocol\FrameVisitor;
use Recoil\Amqp\Protocol\IncomingFrame;
use Recoil\Amqp\Protocol\OutgoingFrame;

final class FlowOkFrame implements IncomingFrame, OutgoingFrame
{
    public $channel;
    public $active; // bit

    public function accept(FrameVisitor $visitor)
    {
        return $visitor->visitChannelFlowOkFrame($this);
    }
}
