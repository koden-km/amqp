<?php

namespace Recoil\Amqp\v091\Transport;

/**
 * The result produced by a successful AMQP handshake.
 *
 * @todo Add server capabilities.
 * @link https://github.com/recoilphp/amqp/issues/2
 */
final class HandshakeResult
{
    /**
     * @var integer The maximum number of channels allowed.
     */
    public $maximumChannelCount;

    /**
     * @var integer The maximum frame size (in bytes).
     */
    public $maximumFrameSize;

    /**
     * @var integer|null The heartbeat interval (in seconds), or null if the heartbeat is disabled.
     */
    public $heartbeatInterval;
}
