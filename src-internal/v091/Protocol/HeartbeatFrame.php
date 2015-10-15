<?php

namespace Recoil\Amqp\v091\Protocol;

final class HeartbeatFrame implements IncomingFrame, OutgoingFrame
{
    public $channel = 0;

    public function create()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    private static $instance;
}
