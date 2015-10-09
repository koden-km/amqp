<?php
namespace Recoil\Amqp\Protocol\v091\Connection;

use Recoil\Amqp\Protocol\FrameVisitor;
use Recoil\Amqp\Protocol\IncomingFrame;

final class BlockedFrame implements IncomingFrame
{
    public $channel;
    public $reason; // shortstr

    public function accept(FrameVisitor $visitor)
    {
        return $visitor->visitConnectionBlockedFrame($this);
    }
}
