<?php
namespace Recoil\Amqp\v091\Protocol\Channel;

use Recoil\Amqp\v091\Protocol\IncomingFrame;
use Recoil\Amqp\v091\Protocol\OutgoingFrame;

final class ChannelFlowOkFrame implements IncomingFrame, OutgoingFrame
{
    public $channel;
    public $active; // bit

    public static function create(
        $channel = 0
      , $active = null
    ) {
        $frame = new self();

        $frame->channel = $channel;
        $frame->active = $active;

        return $frame;
    }
}
