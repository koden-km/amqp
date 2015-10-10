<?php
namespace Recoil\Amqp\v091\Protocol\Connection;

use Recoil\Amqp\v091\Protocol\IncomingFrame;
use Recoil\Amqp\v091\Protocol\IncomingFrameVisitor;

final class ConnectionUnblockedFrame implements IncomingFrame
{
    public $channel;

    public static function create(
        $channel = 0
    ) {
        $frame = new self();

        $frame->channel = $channel;

        return $frame;
    }

    public function acceptIncoming(IncomingFrameVisitor $visitor)
    {
        return $visitor->visitIncomingConnectionUnblockedFrame($this);
    }
}
