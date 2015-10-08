<?php
namespace Recoil\Amqp\Protocol\Basic;

use Recoil\Amqp\Protocol\OutgoingFrame;
use Recoil\Amqp\Protocol\OutgoingFrameVisitor;

final class GetFrame implements OutgoingFrame
{
    public $channel;
    public $reserved; // short
    public $queue; // shortstr
    public $noAck; // bit

    public function acceptOutgoingFrameVisitor(OutgoingFrameVisitor $visitor)
    {
        return $visitor->visitBasicGetFrame($this);
    }
}
