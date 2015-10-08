<?php
namespace Recoil\Amqp\Protocol\Queue;

use Recoil\Amqp\Protocol\IncomingFrame;
use Recoil\Amqp\Protocol\IncomingFrameVisitor;

final class UnbindOkFrame implements IncomingFrame
{
    public $channel;

    public function acceptIncomingFrameVisitor(IncomingFrameVisitor $visitor)
    {
        return $visitor->visitQueueUnbindOkFrame($this);
    }
}
