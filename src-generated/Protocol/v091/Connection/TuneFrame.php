<?php
namespace Recoil\Amqp\Protocol\v091\Connection;

use Recoil\Amqp\Protocol\FrameVisitor;
use Recoil\Amqp\Protocol\IncomingFrame;

final class TuneFrame implements IncomingFrame
{
    public $channel;
    public $channelMax; // short
    public $frameMax; // long
    public $heartbeat; // short

    public function accept(FrameVisitor $visitor)
    {
        return $visitor->visitConnectionTuneFrame($this);
    }
}
