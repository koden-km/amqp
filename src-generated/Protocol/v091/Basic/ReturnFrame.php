<?php
namespace Recoil\Amqp\Protocol\v091\Basic;

use Recoil\Amqp\Protocol\FrameVisitor;
use Recoil\Amqp\Protocol\IncomingFrame;

final class ReturnFrame implements IncomingFrame
{
    public $channel;
    public $replyCode; // short
    public $replyText; // shortstr
    public $exchange; // shortstr
    public $routingKey; // shortstr

    public function accept(FrameVisitor $visitor)
    {
        return $visitor->visitBasicReturnFrame($this);
    }
}
