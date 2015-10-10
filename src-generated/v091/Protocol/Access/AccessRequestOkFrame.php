<?php
namespace Recoil\Amqp\v091\Protocol\Access;

use Recoil\Amqp\v091\Protocol\IncomingFrame;
use Recoil\Amqp\v091\Protocol\IncomingFrameVisitor;

final class AccessRequestOkFrame implements IncomingFrame
{
    public $channel;
    public $reserved1; // short

    public static function create(
        $channel = 0
      , $reserved1 = null
    ) {
        $frame = new self();

        $frame->channel = $channel;
        $frame->reserved1 = null === $reserved1 ? 1 : $reserved1;

        return $frame;
    }

    public function acceptIncoming(IncomingFrameVisitor $visitor)
    {
        return $visitor->visitIncomingAccessRequestOkFrame($this);
    }
}
