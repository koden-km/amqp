<?php
namespace Recoil\Amqp\Protocol\Tx;

use Recoil\Amqp\Protocol\IncomingFrame;
use Recoil\Amqp\Protocol\IncomingFrameVisitor;

final class SelectOkFrame implements IncomingFrame
{
    public $channel;

    public function acceptIncomingFrameVisitor(IncomingFrameVisitor $visitor)
    {
        return $visitor->visitTxSelectOkFrame($this);
    }
}
