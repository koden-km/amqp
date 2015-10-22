<?php
namespace Recoil\Amqp\v091\Protocol\Channel;

use Recoil\Amqp\v091\Protocol\IncomingFrame;

final class ChannelOpenOkFrame implements IncomingFrame
{
    public $frameChannelId;
    public $channelId; // longstr

    public static function create(
        $frameChannelId = 0
      , $channelId = null
    ) {
        $frame = new self();

        $frame->frameChannelId = $frameChannelId;
        $frame->channelId = null === $channelId ? '' : $channelId;

        return $frame;
    }
}
