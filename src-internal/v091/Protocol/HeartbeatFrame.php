<?php
namespace Recoil\Amqp\v091\Protocol;

final class HeartbeatFrame implements IncomingFrame, OutgoingFrame
{
    public $channel = 0;

    public function create()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function acceptIncoming(INcomingFrameVisitor $visitor)
    {
        return $visitor->visitOutgoingHeartbeatFrame($this);
    }

    public function acceptOutgoing(OutgoingFrameVisitor $visitor)
    {
        return $visitor->visitOutgoingHeartbeatFrame($this);
    }

    private static $instance;
}
