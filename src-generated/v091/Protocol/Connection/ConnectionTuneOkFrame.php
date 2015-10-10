<?php
namespace Recoil\Amqp\v091\Protocol\Connection;

use Recoil\Amqp\v091\Protocol\OutgoingFrame;
use Recoil\Amqp\v091\Protocol\OutgoingFrameVisitor;

final class ConnectionTuneOkFrame implements OutgoingFrame
{
    public $channel;
    public $channelMax; // short
    public $frameMax; // long
    public $heartbeat; // short

    public static function create(
        $channel = 0
      , $channelMax = null
      , $frameMax = null
      , $heartbeat = null
    ) {
        $frame = new self();

        $frame->channel = $channel;
        $frame->channelMax = null === $channelMax ? 0 : $channelMax;
        $frame->frameMax = null === $frameMax ? 0 : $frameMax;
        $frame->heartbeat = null === $heartbeat ? 0 : $heartbeat;

        return $frame;
    }

    public function acceptOutgoing(OutgoingFrameVisitor $visitor)
    {
        return $visitor->visitOutgoingConnectionTuneOkFrame($this);
    }
}
