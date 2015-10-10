<?php
namespace Recoil\Amqp\v091\Protocol\Connection;

use Recoil\Amqp\v091\Protocol\IncomingFrame;
use Recoil\Amqp\v091\Protocol\IncomingFrameVisitor;
use Recoil\Amqp\v091\Protocol\OutgoingFrame;
use Recoil\Amqp\v091\Protocol\OutgoingFrameVisitor;

final class ConnectionCloseFrame implements IncomingFrame, OutgoingFrame
{
    public $channel;
    public $replyCode; // short
    public $replyText; // shortstr
    public $classId; // short
    public $methodId; // short

    public static function create(
        $channel = 0
      , $replyCode = null
      , $replyText = null
      , $classId = null
      , $methodId = null
    ) {
        $frame = new self();

        $frame->channel = $channel;
        $frame->replyCode = $replyCode;
        $frame->replyText = null === $replyText ? '' : $replyText;
        $frame->classId = $classId;
        $frame->methodId = $methodId;

        return $frame;
    }

    public function acceptIncoming(IncomingFrameVisitor $visitor)
    {
        return $visitor->visitIncomingConnectionCloseFrame($this);
    }
    public function acceptOutgoing(OutgoingFrameVisitor $visitor)
    {
        return $visitor->visitOutgoingConnectionCloseFrame($this);
    }
}
