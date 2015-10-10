<?php
namespace Recoil\Amqp\v091\Protocol\Basic;

use Recoil\Amqp\v091\Protocol\IncomingFrame;
use Recoil\Amqp\v091\Protocol\IncomingFrameVisitor;

final class BasicReturnFrame implements IncomingFrame
{
    public $channel;
    public $replyCode; // short
    public $replyText; // shortstr
    public $exchange; // shortstr
    public $routingKey; // shortstr

    public static function create(
        $channel = 0
      , $replyCode = null
      , $replyText = null
      , $exchange = null
      , $routingKey = null
    ) {
        $frame = new self();

        $frame->channel = $channel;
        $frame->replyCode = $replyCode;
        $frame->replyText = null === $replyText ? '' : $replyText;
        $frame->exchange = $exchange;
        $frame->routingKey = $routingKey;

        return $frame;
    }

    public function acceptIncoming(IncomingFrameVisitor $visitor)
    {
        return $visitor->visitIncomingBasicReturnFrame($this);
    }
}
