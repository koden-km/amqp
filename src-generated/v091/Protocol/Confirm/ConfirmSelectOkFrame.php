<?php
namespace Recoil\Amqp\v091\Protocol\Confirm;

use Recoil\Amqp\v091\Protocol\IncomingFrame;

final class ConfirmSelectOkFrame implements IncomingFrame
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
