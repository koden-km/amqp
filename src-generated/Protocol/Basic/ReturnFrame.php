<?php
namespace Recoil\Amqp\Protocol\Basic;

use Recoil\Amqp\Protocol\IncomingFrame;
use Recoil\Amqp\Protocol\IncomingFrameVisitor;

final class ReturnFrame implements IncomingFrame
{
    public $channel;
    public $replyCode; // short
    public $replyText; // shortstr
    public $exchange; // shortstr
    public $routingKey; // shortstr

    public function acceptIncomingFrameVisitor(IncomingFrameVisitor $visitor)
    {
        return $visitor->visitBasicReturnFrame($this);
    }
}
