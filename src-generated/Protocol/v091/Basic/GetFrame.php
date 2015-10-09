<?php
namespace Recoil\Amqp\Protocol\v091\Basic;

use Recoil\Amqp\Protocol\FrameVisitor;
use Recoil\Amqp\Protocol\OutgoingFrame;

final class GetFrame implements OutgoingFrame
{
    public $channel;
    public $reserved1; // short
    public $queue; // shortstr
    public $noAck; // bit

    public function accept(FrameVisitor $visitor)
    {
        return $visitor->visitBasicGetFrame($this);
    }
}
