<?php
namespace Recoil\Amqp\v091\Protocol\Connection;

use Recoil\Amqp\v091\Protocol\IncomingFrame;

final class ConnectionTuneFrame implements IncomingFrame
{
    public $frameChannelId;
    public $channelMax; // short
    public $frameMax; // long
    public $heartbeat; // short

    public static function create(
        $frameChannelId = 0
      , $channelMax = null
      , $frameMax = null
      , $heartbeat = null
    ) {
        $frame = new self();

        $frame->frameChannelId = $frameChannelId;
        $frame->channelMax = null === $channelMax ? 0 : $channelMax;
        $frame->frameMax = null === $frameMax ? 0 : $frameMax;
        $frame->heartbeat = null === $heartbeat ? 0 : $heartbeat;

        return $frame;
    }
}
