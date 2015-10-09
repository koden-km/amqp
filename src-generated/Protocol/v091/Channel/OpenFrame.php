<?php
namespace Recoil\Amqp\Protocol\v091\Channel;

use Recoil\Amqp\Protocol\FrameVisitor;
use Recoil\Amqp\Protocol\OutgoingFrame;

final class OpenFrame implements OutgoingFrame
{
    public $channel;
    public $outOfBand; // shortstr

    public function accept(FrameVisitor $visitor)
    {
        return $visitor->visitChannelOpenFrame($this);
    }
}
