<?php
namespace Recoil\Amqp\v091\Protocol\Queue;

use Recoil\Amqp\v091\Protocol\IncomingFrame;

final class QueueDeclareOkFrame implements IncomingFrame
{
    public $frameChannelId;
    public $queue; // shortstr
    public $messageCount; // long
    public $consumerCount; // long

    public static function create(
        $frameChannelId = 0
      , $queue = null
      , $messageCount = null
      , $consumerCount = null
    ) {
        $frame = new self();

        $frame->frameChannelId = $frameChannelId;
        $frame->queue = $queue;
        $frame->messageCount = $messageCount;
        $frame->consumerCount = $consumerCount;

        return $frame;
    }
}
