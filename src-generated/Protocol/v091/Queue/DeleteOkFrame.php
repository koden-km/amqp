<?php
namespace Recoil\Amqp\Protocol\v091\Queue;

use Recoil\Amqp\Protocol\FrameVisitor;
use Recoil\Amqp\Protocol\IncomingFrame;

final class DeleteOkFrame implements IncomingFrame
{
    public $channel;
    public $messageCount; // long

    public function accept(FrameVisitor $visitor)
    {
        return $visitor->visitQueueDeleteOkFrame($this);
    }
}
