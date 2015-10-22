<?php
namespace Recoil\Amqp\v091\Protocol\Confirm;

use Recoil\Amqp\v091\Protocol\OutgoingFrame;

final class ConfirmSelectFrame implements OutgoingFrame
{
    public $frameChannelId;
    public $nowait; // bit

    public static function create(
        $frameChannelId = 0
      , $nowait = null
    ) {
        $frame = new self();

        $frame->frameChannelId = $frameChannelId;
        $frame->nowait = null === $nowait ? false : $nowait;

        return $frame;
    }
}
