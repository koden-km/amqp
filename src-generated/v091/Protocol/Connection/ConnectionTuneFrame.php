<?php
namespace Recoil\Amqp\v091\Protocol\Connection;

use Recoil\Amqp\v091\Protocol\IncomingFrame;
use Recoil\Amqp\v091\Protocol\IncomingFrameVisitor;

final class ConnectionTuneFrame implements IncomingFrame
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

    public function acceptIncoming(IncomingFrameVisitor $visitor)
    {
        return $visitor->visitIncomingConnectionTuneFrame($this);
    }
}
