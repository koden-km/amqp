<?php
namespace Recoil\Amqp\v091\Protocol\Tx;

use Recoil\Amqp\v091\Protocol\OutgoingFrame;
use Recoil\Amqp\v091\Protocol\OutgoingFrameVisitor;

final class TxRollbackFrame implements OutgoingFrame
{
    public $channel;

    public static function create(
        $channel = 0
    ) {
        $frame = new self();

        $frame->channel = $channel;

        return $frame;
    }

    public function acceptOutgoing(OutgoingFrameVisitor $visitor)
    {
        return $visitor->visitOutgoingTxRollbackFrame($this);
    }
}
