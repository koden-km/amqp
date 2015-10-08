<?php
namespace Recoil\Amqp\Protocol\Connection;

use Recoil\Amqp\Protocol\IncomingFrame;
use Recoil\Amqp\Protocol\IncomingFrameVisitor;

final class TuneFrame implements IncomingFrame
{
    public $channel;
    public $channelMax; // short
    public $frameMax; // long
    public $heartbeat; // short

    public function acceptIncomingFrameVisitor(IncomingFrameVisitor $visitor)
    {
        return $visitor->visitConnectionTuneFrame($this);
    }
}
