<?php
namespace Recoil\Amqp\v091\Protocol\Channel;

use Recoil\Amqp\v091\Protocol\IncomingFrame;
use Recoil\Amqp\v091\Protocol\OutgoingFrame;

final class ChannelCloseOkFrame implements IncomingFrame, OutgoingFrame
{
    public $channel;

    public static function create(
        $channel = 0
    ) {
        $frame = new self();

        $frame->channel = $channel;

        return $frame;
    }
}
