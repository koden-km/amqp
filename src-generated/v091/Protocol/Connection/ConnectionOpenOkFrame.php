<?php
namespace Recoil\Amqp\v091\Protocol\Connection;

use Recoil\Amqp\v091\Protocol\IncomingFrame;

final class ConnectionOpenOkFrame implements IncomingFrame
{
    public $channel;
    public $knownHosts; // shortstr

    public static function create(
        $channel = 0
      , $knownHosts = null
    ) {
        $frame = new self();

        $frame->channel = $channel;
        $frame->knownHosts = null === $knownHosts ? '' : $knownHosts;

        return $frame;
    }
}
