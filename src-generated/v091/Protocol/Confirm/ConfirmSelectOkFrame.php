<?php
namespace Recoil\Amqp\v091\Protocol\Confirm;

use Recoil\Amqp\v091\Protocol\IncomingFrame;

final class ConfirmSelectOkFrame implements IncomingFrame
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
