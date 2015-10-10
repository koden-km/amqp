<?php
namespace Recoil\Amqp\v091\Protocol\Basic;

use Recoil\Amqp\v091\Protocol\IncomingFrame;
use Recoil\Amqp\v091\Protocol\IncomingFrameVisitor;

final class BasicGetEmptyFrame implements IncomingFrame
{
    public $channel;
    public $clusterId; // shortstr

    public static function create(
        $channel = 0
      , $clusterId = null
    ) {
        $frame = new self();

        $frame->channel = $channel;
        $frame->clusterId = null === $clusterId ? '' : $clusterId;

        return $frame;
    }

    public function acceptIncoming(IncomingFrameVisitor $visitor)
    {
        return $visitor->visitIncomingBasicGetEmptyFrame($this);
    }
}
