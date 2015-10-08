<?php
namespace Recoil\Amqp\Protocol\Queue;

use Recoil\Amqp\Protocol\IncomingFrame;
use Recoil\Amqp\Protocol\IncomingFrameVisitor;

final class PurgeOkFrame implements IncomingFrame
{
    public $channel;
    public $messageCount; // long

    public function acceptIncomingFrameVisitor(IncomingFrameVisitor $visitor)
    {
        return $visitor->visitQueuePurgeOkFrame($this);
    }
}
