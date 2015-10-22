<?php
namespace Recoil\Amqp\v091\Protocol\Basic;

use Recoil\Amqp\v091\Protocol\OutgoingFrame;

final class BasicQosFrame implements OutgoingFrame
{
    public $frameChannelId;
    public $prefetchSize; // long
    public $prefetchCount; // short
    public $global; // bit

    public static function create(
        $frameChannelId = 0
      , $prefetchSize = null
      , $prefetchCount = null
      , $global = null
    ) {
        $frame = new self();

        $frame->frameChannelId = $frameChannelId;
        $frame->prefetchSize = null === $prefetchSize ? 0 : $prefetchSize;
        $frame->prefetchCount = null === $prefetchCount ? 0 : $prefetchCount;
        $frame->global = null === $global ? false : $global;

        return $frame;
    }
}
