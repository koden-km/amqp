<?php
namespace Recoil\Amqp\v091\Protocol\Basic;

use Recoil\Amqp\v091\Protocol\IncomingFrame;

final class BasicGetEmptyFrame implements IncomingFrame
{
    public $channel;
    public $clusterId; // shortstr

    public static function create(
        $channel = 0
      , $clusterId = null
    ) {
        $frame = new self();

        $frame->channel = $channel;
        $frame->clusterId = null === $clusterId ? '' : $clusterId;

        return $frame;
    }
}
