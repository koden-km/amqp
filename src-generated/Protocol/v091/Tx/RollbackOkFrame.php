<?php
namespace Recoil\Amqp\Protocol\v091\Tx;

use Recoil\Amqp\Protocol\FrameVisitor;
use Recoil\Amqp\Protocol\IncomingFrame;

final class RollbackOkFrame implements IncomingFrame
{
    public $channel;

    public function accept(FrameVisitor $visitor)
    {
        return $visitor->visitTxRollbackOkFrame($this);
    }
}
