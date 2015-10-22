<?php
namespace Recoil\Amqp\v091\Protocol\Basic;

use Recoil\Amqp\v091\Protocol\IncomingFrame;

final class BasicGetEmptyFrame implements IncomingFrame
{
    public $frameChannelId;
    public $clusterId; // shortstr

    public static function create(
        $frameChannelId = 0
      , $clusterId = null
    ) {
        $frame = new self();

        $frame->frameChannelId = $frameChannelId;
        $frame->clusterId = null === $clusterId ? '' : $clusterId;

        return $frame;
    }
}
