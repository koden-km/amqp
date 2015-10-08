<?php
namespace Recoil\Amqp\Protocol\Tx;

use Recoil\Amqp\Protocol\OutgoingFrame;
use Recoil\Amqp\Protocol\OutgoingFrameVisitor;

final class CommitFrame implements OutgoingFrame
{
    public $channel;

    public function acceptOutgoingFrameVisitor(OutgoingFrameVisitor $visitor)
    {
        return $visitor->visitTxCommitFrame($this);
    }
}
