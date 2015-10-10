<?php
namespace Recoil\Amqp\v091\Protocol\Connection;

use Recoil\Amqp\v091\Protocol\IncomingFrame;
use Recoil\Amqp\v091\Protocol\IncomingFrameVisitor;

final class ConnectionSecureFrame implements IncomingFrame
{
    public $channel;
    public $challenge; // longstr

    public static function create(
        $channel = 0
      , $challenge = null
    ) {
        $frame = new self();

        $frame->channel = $channel;
        $frame->challenge = $challenge;

        return $frame;
    }

    public function acceptIncoming(IncomingFrameVisitor $visitor)
    {
        return $visitor->visitIncomingConnectionSecureFrame($this);
    }
}
