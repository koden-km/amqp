<?php
namespace Recoil\Amqp\Protocol\v091\Basic;

use Recoil\Amqp\Protocol\FrameVisitor;
use Recoil\Amqp\Protocol\IncomingFrame;

final class CancelOkFrame implements IncomingFrame
{
    public $channel;
    public $consumerTag; // shortstr

    public function accept(FrameVisitor $visitor)
    {
        return $visitor->visitBasicCancelOkFrame($this);
    }
}
