<?php
namespace Recoil\Amqp\v091\Protocol\Channel;

use Recoil\Amqp\v091\Protocol\OutgoingFrame;

final class ChannelOpenFrame implements OutgoingFrame
{
    public $frameChannelId;
    public $outOfBand; // shortstr

    public static function create(
        $frameChannelId = 0
      , $outOfBand = null
    ) {
        $frame = new self();

        $frame->frameChannelId = $frameChannelId;
        $frame->outOfBand = null === $outOfBand ? '' : $outOfBand;

        return $frame;
    }
}
