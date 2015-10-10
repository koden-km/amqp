<?php
namespace Recoil\Amqp\v091\Protocol\Connection;

use Recoil\Amqp\v091\Protocol\IncomingFrame;
use Recoil\Amqp\v091\Protocol\IncomingFrameVisitor;
use Recoil\Amqp\v091\Protocol\OutgoingFrame;
use Recoil\Amqp\v091\Protocol\OutgoingFrameVisitor;

final class ConnectionCloseOkFrame implements IncomingFrame, OutgoingFrame
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
        return $visitor->visitIncomingConnectionCloseOkFrame($this);
    }
    public function acceptOutgoing(OutgoingFrameVisitor $visitor)
    {
        return $visitor->visitOutgoingConnectionCloseOkFrame($this);
    }
}
