<?php
namespace Recoil\Amqp\v091\Protocol\Tx;

use Recoil\Amqp\v091\Protocol\OutgoingFrame;

final class TxSelectFrame implements OutgoingFrame
{
    public $frameChannelId;

    public static function create(
        $frameChannelId = 0
    ) {
        $frame = new self();

        $frame->frameChannelId = $frameChannelId;

        return $frame;
    }
}
