<?php
namespace Recoil\Amqp\Protocol\v091\Basic;

use Recoil\Amqp\Protocol\FrameVisitor;
use Recoil\Amqp\Protocol\OutgoingFrame;

final class CancelFrame implements OutgoingFrame
{
    public $channel;
    public $consumerTag; // shortstr
    public $nowait; // bit

    public function accept(FrameVisitor $visitor)
    {
        return $visitor->visitBasicCancelFrame($this);
    }
}
