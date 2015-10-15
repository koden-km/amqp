<?php
namespace Recoil\Amqp\v091\Protocol\Basic;

use Recoil\Amqp\v091\Protocol\IncomingFrame;

final class BasicConsumeOkFrame implements IncomingFrame
{
    public $channel;
    public $consumerTag; // shortstr

    public static function create(
        $channel = 0
      , $consumerTag = null
    ) {
        $frame = new self();

        $frame->channel = $channel;
        $frame->consumerTag = $consumerTag;

        return $frame;
    }
}
