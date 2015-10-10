<?php
namespace Recoil\Amqp\v091\Protocol\Connection;

use Recoil\Amqp\v091\Protocol\IncomingFrame;
use Recoil\Amqp\v091\Protocol\IncomingFrameVisitor;

final class ConnectionBlockedFrame implements IncomingFrame
{
    public $channel;
    public $reason; // shortstr

    public static function create(
        $channel = 0
      , $reason = null
    ) {
        $frame = new self();

        $frame->channel = $channel;
        $frame->reason = null === $reason ? '' : $reason;

        return $frame;
    }

    public function acceptIncoming(IncomingFrameVisitor $visitor)
    {
        return $visitor->visitIncomingConnectionBlockedFrame($this);
    }
}
