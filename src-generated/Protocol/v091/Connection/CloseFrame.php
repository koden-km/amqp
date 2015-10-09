<?php
namespace Recoil\Amqp\Protocol\v091\Connection;

use Recoil\Amqp\Protocol\FrameVisitor;
use Recoil\Amqp\Protocol\IncomingFrame;
use Recoil\Amqp\Protocol\OutgoingFrame;

final class CloseFrame implements IncomingFrame, OutgoingFrame
{
    public $channel;
    public $replyCode; // short
    public $replyText; // shortstr
    public $classId; // short
    public $methodId; // short

    public function accept(FrameVisitor $visitor)
    {
        return $visitor->visitConnectionCloseFrame($this);
    }
}
