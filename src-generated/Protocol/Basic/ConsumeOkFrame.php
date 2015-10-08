<?php
namespace Recoil\Amqp\Protocol\Basic;

use Recoil\Amqp\Protocol\IncomingFrame;
use Recoil\Amqp\Protocol\IncomingFrameVisitor;

final class ConsumeOkFrame implements IncomingFrame
{
    public $channel;
    public $consumerTag; // shortstr

    public function acceptIncomingFrameVisitor(IncomingFrameVisitor $visitor)
    {
        return $visitor->visitBasicConsumeOkFrame($this);
    }
}
