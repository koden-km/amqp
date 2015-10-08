<?php
namespace Recoil\Amqp\Protocol\Channel;

use Recoil\Amqp\Protocol\OutgoingFrame;
use Recoil\Amqp\Protocol\OutgoingFrameVisitor;

final class OpenFrame implements OutgoingFrame
{
    public $channel;
    public $outOfBand; // shortstr

    public function acceptOutgoingFrameVisitor(OutgoingFrameVisitor $visitor)
    {
        return $visitor->visitChannelOpenFrame($this);
    }
}
