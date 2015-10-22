<?php
namespace Recoil\Amqp\v091\Protocol\Exchange;

use Recoil\Amqp\v091\Protocol\OutgoingFrame;

final class ExchangeDeleteFrame implements OutgoingFrame
{
    public $frameChannelId;
    public $reserved1; // short
    public $exchange; // shortstr
    public $ifUnused; // bit
    public $nowait; // bit

    public static function create(
        $frameChannelId = 0
      , $reserved1 = null
      , $exchange = null
      , $ifUnused = null
      , $nowait = null
    ) {
        $frame = new self();

        $frame->frameChannelId = $frameChannelId;
        $frame->reserved1 = null === $reserved1 ? 0 : $reserved1;
        $frame->exchange = $exchange;
        $frame->ifUnused = null === $ifUnused ? false : $ifUnused;
        $frame->nowait = null === $nowait ? false : $nowait;

        return $frame;
    }
}
