<?php
namespace Recoil\Amqp\v091\Protocol\Queue;

use Recoil\Amqp\v091\Protocol\IncomingFrame;

final class QueueDeleteOkFrame implements IncomingFrame
{
    public $frameChannelId;
    public $messageCount; // long

    public static function create(
        $frameChannelId = 0
      , $messageCount = null
    ) {
        $frame = new self();

        $frame->frameChannelId = $frameChannelId;
        $frame->messageCount = $messageCount;

        return $frame;
    }
}
