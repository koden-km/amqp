<?php
namespace Recoil\Amqp\Protocol\v091\Queue;

use Recoil\Amqp\Protocol\FrameVisitor;
use Recoil\Amqp\Protocol\IncomingFrame;

final class PurgeOkFrame implements IncomingFrame
{
    public $channel;
    public $messageCount; // long

    public function accept(FrameVisitor $visitor)
    {
        return $visitor->visitQueuePurgeOkFrame($this);
    }
}
