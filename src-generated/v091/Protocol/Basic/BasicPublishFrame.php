<?php
namespace Recoil\Amqp\v091\Protocol\Basic;

use Recoil\Amqp\v091\Protocol\OutgoingFrame;

final class BasicPublishFrame implements OutgoingFrame
{
    public $frameChannelId;
    public $reserved1; // short
    public $exchange; // shortstr
    public $routingKey; // shortstr
    public $mandatory; // bit
    public $immediate; // bit

    public static function create(
        $frameChannelId = 0
      , $reserved1 = null
      , $exchange = null
      , $routingKey = null
      , $mandatory = null
      , $immediate = null
    ) {
        $frame = new self();

        $frame->frameChannelId = $frameChannelId;
        $frame->reserved1 = null === $reserved1 ? 0 : $reserved1;
        $frame->exchange = null === $exchange ? '' : $exchange;
        $frame->routingKey = null === $routingKey ? '' : $routingKey;
        $frame->mandatory = null === $mandatory ? false : $mandatory;
        $frame->immediate = null === $immediate ? false : $immediate;

        return $frame;
    }
}
