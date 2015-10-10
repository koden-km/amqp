<?php
namespace Recoil\Amqp\v091\Protocol\Connection;

use Recoil\Amqp\v091\Protocol\OutgoingFrame;
use Recoil\Amqp\v091\Protocol\OutgoingFrameVisitor;

final class ConnectionOpenFrame implements OutgoingFrame
{
    public $channel;
    public $virtualHost; // shortstr
    public $capabilities; // shortstr
    public $insist; // bit

    public static function create(
        $channel = 0
      , $virtualHost = null
      , $capabilities = null
      , $insist = null
    ) {
        $frame = new self();

        $frame->channel = $channel;
        $frame->virtualHost = null === $virtualHost ? '/' : $virtualHost;
        $frame->capabilities = null === $capabilities ? '' : $capabilities;
        $frame->insist = null === $insist ? false : $insist;

        return $frame;
    }

    public function acceptOutgoing(OutgoingFrameVisitor $visitor)
    {
        return $visitor->visitOutgoingConnectionOpenFrame($this);
    }
}
