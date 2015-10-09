<?php
namespace Recoil\Amqp\Protocol\v091\Connection;

use Recoil\Amqp\Protocol\FrameVisitor;
use Recoil\Amqp\Protocol\OutgoingFrame;

final class TuneOkFrame implements OutgoingFrame
{
    public $channel;
    public $channelMax; // short
    public $frameMax; // long
    public $heartbeat; // short

    public function accept(FrameVisitor $visitor)
    {
        return $visitor->visitConnectionTuneOkFrame($this);
    }
}
