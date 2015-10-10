<?php
namespace Recoil\Amqp\v091\Protocol\Connection;

use Recoil\Amqp\v091\Protocol\IncomingFrame;
use Recoil\Amqp\v091\Protocol\IncomingFrameVisitor;

final class ConnectionOpenOkFrame implements IncomingFrame
{
    public $channel;
    public $knownHosts; // shortstr

    public static function create(
        $channel = 0
      , $knownHosts = null
    ) {
        $frame = new self();

        $frame->channel = $channel;
        $frame->knownHosts = null === $knownHosts ? '' : $knownHosts;

        return $frame;
    }

    public function acceptIncoming(IncomingFrameVisitor $visitor)
    {
        return $visitor->visitIncomingConnectionOpenOkFrame($this);
    }
}
