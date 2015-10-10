<?php
namespace Recoil\Amqp\v091\Protocol\Tx;

use Recoil\Amqp\v091\Protocol\IncomingFrame;
use Recoil\Amqp\v091\Protocol\IncomingFrameVisitor;

final class TxCommitOkFrame implements IncomingFrame
{
    public $channel;

    public static function create(
        $channel = 0
    ) {
        $frame = new self();

        $frame->channel = $channel;

        return $frame;
    }

    public function acceptIncoming(IncomingFrameVisitor $visitor)
    {
        return $visitor->visitIncomingTxCommitOkFrame($this);
    }
}
