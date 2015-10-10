<?php
namespace Recoil\Amqp\v091\Protocol\Channel;

use Recoil\Amqp\v091\Protocol\OutgoingFrame;
use Recoil\Amqp\v091\Protocol\OutgoingFrameVisitor;

final class ChannelOpenFrame implements OutgoingFrame
{
    public $channel;
    public $outOfBand; // shortstr

    public static function create(
        $channel = 0
      , $outOfBand = null
    ) {
        $frame = new self();

        $frame->channel = $channel;
        $frame->outOfBand = null === $outOfBand ? '' : $outOfBand;

        return $frame;
    }

    public function acceptOutgoing(OutgoingFrameVisitor $visitor)
    {
        return $visitor->visitOutgoingChannelOpenFrame($this);
    }
}
