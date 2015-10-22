<?php

namespace Recoil\Amqp\Exception;

use Exception;
use RuntimeException;

/**
 * An exception used to indicate problems establishing or using an AMQP channel.
 */
final class ChannelException extends RuntimeException implements RecoilAmqpException
{
    /**
     * Create an exception that indicates an attempt to use a channel that has
     * already been closed.
     *
     * @param integer        $channelId The channel ID.
     * @param Exception|null $previous  The exception that caused this exception, if any.
     *
     * @return ChannelException
     */
    public static function notOpen($channelId, Exception $previous = null)
    {
        return new self(
            sprintf(
                'Unable to use channel #%d, it is closed.',
                $channelId
            ),
            0,
            $previous
        );
    }

    /**
     * Create an exception that indicates the maximum number of channels are
     * already in use.
     *
     * @param Exception|null $previous The exception that caused this exception, if any.
     *
     * @return ChannelException
     */
    public static function noAvailableChannels(Exception $previous = null)
    {
        return new self(
            'Unable to open new channel, the maximum number of channels has been reached.',
            0,
            $previous
        );
    }

    /**
     * Create an exception that indicates an unexpected closure of an AMQP channel.
     *
     * @param integer        $channelId The channel ID.
     * @param Exception|null $previous  The exception that caused this exception, if any.
     *
     * @return ConnectionException
     */
    public static function closedUnexpectedly(
        $channelId,
        Exception $previous = null
    ) {
        return new self(
            sprintf(
                'Channel #%d was closed unexpectedly.',
                $channelId
            ),
            0,
            $previous
        );
    }
}
