<?php
namespace Recoil\Amqp\v091\Protocol\Basic;

use Recoil\Amqp\v091\Protocol\OutgoingFrame;

final class BasicGetFrame implements OutgoingFrame
{
    public $channel;
    public $reserved1; // short
    public $queue; // shortstr
    public $noAck; // bit

    public static function create(
        $channel = 0
      , $reserved1 = null
      , $queue = null
      , $noAck = null
    ) {
        $frame = new self();

        $frame->channel = $channel;
        $frame->reserved1 = null === $reserved1 ? 0 : $reserved1;
        $frame->queue = null === $queue ? '' : $queue;
        $frame->noAck = null === $noAck ? false : $noAck;

        return $frame;
    }
}
