<?php
namespace Recoil\Amqp\v091\Protocol\Connection;

use Recoil\Amqp\v091\Protocol\IncomingFrame;

final class ConnectionOpenOkFrame implements IncomingFrame
{
    public $frameChannelId;
    public $knownHosts; // shortstr

    public static function create(
        $frameChannelId = 0
      , $knownHosts = null
    ) {
        $frame = new self();

        $frame->frameChannelId = $frameChannelId;
        $frame->knownHosts = null === $knownHosts ? '' : $knownHosts;

        return $frame;
    }
}
