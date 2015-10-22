<?php
namespace Recoil\Amqp\v091\Protocol\Basic;

use Recoil\Amqp\v091\Protocol\OutgoingFrame;

final class BasicRejectFrame implements OutgoingFrame
{
    public $frameChannelId;
    public $deliveryTag; // longlong
    public $requeue; // bit

    public static function create(
        $frameChannelId = 0
      , $deliveryTag = null
      , $requeue = null
    ) {
        $frame = new self();

        $frame->frameChannelId = $frameChannelId;
        $frame->deliveryTag = $deliveryTag;
        $frame->requeue = null === $requeue ? true : $requeue;

        return $frame;
    }
}
