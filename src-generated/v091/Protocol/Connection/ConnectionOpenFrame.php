<?php
namespace Recoil\Amqp\v091\Protocol\Connection;

use Recoil\Amqp\v091\Protocol\OutgoingFrame;

final class ConnectionOpenFrame implements OutgoingFrame
{
    public $frameChannelId;
    public $virtualHost; // shortstr
    public $capabilities; // shortstr
    public $insist; // bit

    public static function create(
        $frameChannelId = 0
      , $virtualHost = null
      , $capabilities = null
      , $insist = null
    ) {
        $frame = new self();

        $frame->frameChannelId = $frameChannelId;
        $frame->virtualHost = null === $virtualHost ? '/' : $virtualHost;
        $frame->capabilities = null === $capabilities ? '' : $capabilities;
        $frame->insist = null === $insist ? false : $insist;

        return $frame;
    }
}
