<?php
namespace Recoil\Amqp\v091\Protocol\Access;

use Recoil\Amqp\v091\Protocol\IncomingFrame;

final class AccessRequestOkFrame implements IncomingFrame
{
    public $frameChannelId;
    public $reserved1; // short

    public static function create(
        $frameChannelId = 0
      , $reserved1 = null
    ) {
        $frame = new self();

        $frame->frameChannelId = $frameChannelId;
        $frame->reserved1 = null === $reserved1 ? 1 : $reserved1;

        return $frame;
    }
}
