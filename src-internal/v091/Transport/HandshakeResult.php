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
     * The maximum number of channels.
     *
     * AMQP channel ID is 2 bytes, but zero is reserved for connection-level
     * communication.
     */
    const MAX_CHANNELS = 0xffff - 1;

    /**
     * The maximum frame size the client supports.
     *
     * Note: RabbitMQ's default is 0x20000 (128 KB), our limit is higher to
     * allow for some server-side configurability.
     */
    const MAX_FRAME_SIZE = 0x80000; // 512 KB

    /**
     * @var integer The maximum number of channels allowed.
     */
    public $maximumChannelCount;

    /**
     * @var integer The maximum frame size (in bytes).
     */
    public $maximumFrameSize;

    /**
     * @var integer|null The heartbeat interval (in seconds), or null if the
     *                   heartbeat is disabled.
     */
    public $heartbeatInterval;

    /**
     * @param $maximumChannelCount integer      The maximum number of channels
     *                                          allowed.
     * @param $maximumFrameSize    integer      The maximum frame size (in bytes).
     * @param $heartbeatInterval   integer|null The heartbeat interval (in seconds),
     *                                          or null if the heartbeat is disabled.
     */
    public function __construct(
        $maximumChannelCount = self::MAX_CHANNELS,
        $maximumFrameSize = self::MAX_FRAME_SIZE,
        $heartbeatInterval = null
    ) {
        $this->maximumChannelCount = $maximumChannelCount;
        $this->maximumFrameSize = $maximumFrameSize;
        $this->heartbeatInterval = $heartbeatInterval;
    }
}
