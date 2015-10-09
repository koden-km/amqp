<?php
namespace Recoil\Amqp\Protocol\v091\Exchange;

use Recoil\Amqp\Protocol\FrameVisitor;
use Recoil\Amqp\Protocol\IncomingFrame;

final class UnbindOkFrame implements IncomingFrame
{
    public $channel;

    public function accept(FrameVisitor $visitor)
    {
        return $visitor->visitExchangeUnbindOkFrame($this);
    }
}
