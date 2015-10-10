<?php
namespace Recoil\Amqp\v091\Protocol\Queue;

use Recoil\Amqp\v091\Protocol\IncomingFrame;
use Recoil\Amqp\v091\Protocol\IncomingFrameVisitor;

final class QueueDeleteOkFrame implements IncomingFrame
{
    public $channel;
    public $messageCount; // long

    public static function create(
        $channel = 0
      , $messageCount = null
    ) {
        $frame = new self();

        $frame->channel = $channel;
        $frame->messageCount = $messageCount;

        return $frame;
    }

    public function acceptIncoming(IncomingFrameVisitor $visitor)
    {
        return $visitor->visitIncomingQueueDeleteOkFrame($this);
    }
}
