<?php
namespace Recoil\Amqp\Protocol\Connection;

use Recoil\Amqp\Protocol\IncomingFrame;
use Recoil\Amqp\Protocol\IncomingFrameVisitor;

final class UnblockedFrame implements IncomingFrame
{
    public $channel;

    public function acceptIncomingFrameVisitor(IncomingFrameVisitor $visitor)
    {
        return $visitor->visitConnectionUnblockedFrame($this);
    }
}
