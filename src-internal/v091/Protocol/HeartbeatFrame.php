<?php

namespace Recoil\Amqp\v091\Protocol;

/**
 * The heartbeat frame is sent by both the client and the server in order to
 * keep the connection alive.
 */
final class HeartbeatFrame implements IncomingFrame, OutgoingFrame
{
    /**
     * @var integer Heartbeat frames are only sent on channel zero.
     */
    public $channel = 0;

    /**
     * Create/get a heartbeat frame.
     *
     * @return HeartbeatFrame
     */
    public static function create()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * @var self The singleton instance of a heartbeat frame.
     */
    private static $instance;
}
