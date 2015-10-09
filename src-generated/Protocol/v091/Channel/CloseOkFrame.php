<?php
namespace Recoil\Amqp\Protocol\v091\Channel;

use Recoil\Amqp\Protocol\FrameVisitor;
use Recoil\Amqp\Protocol\IncomingFrame;
use Recoil\Amqp\Protocol\OutgoingFrame;

final class CloseOkFrame implements IncomingFrame, OutgoingFrame
{
    public $channel;

    public function accept(FrameVisitor $visitor)
    {
        return $visitor->visitChannelCloseOkFrame($this);
    }
}
