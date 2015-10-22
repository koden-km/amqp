<?php
namespace Recoil\Amqp\v091\Protocol\Basic;

use Recoil\Amqp\v091\Protocol\IncomingFrame;

final class BasicReturnFrame implements IncomingFrame
{
    public $frameChannelId;
    public $replyCode; // short
    public $replyText; // shortstr
    public $exchange; // shortstr
    public $routingKey; // shortstr

    public static function create(
        $frameChannelId = 0
      , $replyCode = null
      , $replyText = null
      , $exchange = null
      , $routingKey = null
    ) {
        $frame = new self();

        $frame->frameChannelId = $frameChannelId;
        $frame->replyCode = $replyCode;
        $frame->replyText = null === $replyText ? '' : $replyText;
        $frame->exchange = $exchange;
        $frame->routingKey = $routingKey;

        return $frame;
    }
}
