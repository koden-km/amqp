<?php
namespace Recoil\Amqp\Protocol\Connection;

use Recoil\Amqp\Protocol\OutgoingFrame;
use Recoil\Amqp\Protocol\OutgoingFrameVisitor;

final class TuneOkFrame implements OutgoingFrame
{
    public $channel;
    public $channelMax; // short
    public $frameMax; // long
    public $heartbeat; // short

    public function acceptOutgoingFrameVisitor(OutgoingFrameVisitor $visitor)
    {
        return $visitor->visitConnectionTuneOkFrame($this);
    }
}
