<?php
namespace Recoil\Amqp\v091\Protocol\Basic;

use Recoil\Amqp\v091\Protocol\OutgoingFrame;

final class BasicCancelFrame implements OutgoingFrame
{
    public $channel;
    public $consumerTag; // shortstr
    public $nowait; // bit

    public static function create(
        $channel = 0
      , $consumerTag = null
      , $nowait = null
    ) {
        $frame = new self();

        $frame->channel = $channel;
        $frame->consumerTag = $consumerTag;
        $frame->nowait = null === $nowait ? false : $nowait;

        return $frame;
    }
}
