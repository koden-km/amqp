<?php
namespace Recoil\Amqp\v091\Protocol\Channel;

use Recoil\Amqp\v091\Protocol\OutgoingFrame;

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
}
