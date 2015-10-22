<?php
namespace Recoil\Amqp\v091\Protocol\Channel;

use Recoil\Amqp\v091\Protocol\IncomingFrame;
use Recoil\Amqp\v091\Protocol\OutgoingFrame;

final class ChannelFlowFrame implements IncomingFrame, OutgoingFrame
{
    public $frameChannelId;
    public $active; // bit

    public static function create(
        $frameChannelId = 0
      , $active = null
    ) {
        $frame = new self();

        $frame->frameChannelId = $frameChannelId;
        $frame->active = $active;

        return $frame;
    }
}
