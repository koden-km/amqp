<?php
namespace Recoil\Amqp\Protocol\Connection;

use Recoil\Amqp\Protocol\IncomingFrame;
use Recoil\Amqp\Protocol\IncomingFrameVisitor;
use Recoil\Amqp\Protocol\OutgoingFrame;
use Recoil\Amqp\Protocol\OutgoingFrameVisitor;

final class CloseFrame implements IncomingFrame, OutgoingFrame
{
    public $channel;
    public $replyCode; // short
    public $replyText; // shortstr
    public $classId; // short
    public $methodId; // short

    public function acceptIncomingFrameVisitor(IncomingFrameVisitor $visitor)
    {
        return $visitor->visitConnectionCloseFrame($this);
    }

    public function acceptOutgoingFrameVisitor(OutgoingFrameVisitor $visitor)
    {
        return $visitor->visitConnectionCloseFrame($this);
    }
}
