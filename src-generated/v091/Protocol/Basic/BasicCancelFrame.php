<?php
namespace Recoil\Amqp\v091\Protocol\Basic;

use Recoil\Amqp\v091\Protocol\OutgoingFrame;

final class BasicCancelFrame implements OutgoingFrame
{
    public $frameChannelId;
    public $consumerTag; // shortstr
    public $nowait; // bit

    public static function create(
        $frameChannelId = 0
      , $consumerTag = null
      , $nowait = null
    ) {
        $frame = new self();

        $frame->frameChannelId = $frameChannelId;
        $frame->consumerTag = $consumerTag;
        $frame->nowait = null === $nowait ? false : $nowait;

        return $frame;
    }
}
