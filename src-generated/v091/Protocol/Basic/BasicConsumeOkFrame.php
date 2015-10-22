<?php
namespace Recoil\Amqp\v091\Protocol\Basic;

use Recoil\Amqp\v091\Protocol\IncomingFrame;

final class BasicConsumeOkFrame implements IncomingFrame
{
    public $frameChannelId;
    public $consumerTag; // shortstr

    public static function create(
        $frameChannelId = 0
      , $consumerTag = null
    ) {
        $frame = new self();

        $frame->frameChannelId = $frameChannelId;
        $frame->consumerTag = $consumerTag;

        return $frame;
    }
}
