<?php
namespace Recoil\Amqp;

use Icecave\Flip\AbstractOption;

/**
 * Options used when declaring a queue.
 */
final class QueueOption extends AbstractOption
{
    /**
     * Confirm that the queue exists and matches the configured options, but do
     * not create it.
     */
    const PASSIVE = 'passive';

    /**
     * Persist the queue (but not necessarily the messages on it) across server
     * restarts.
     */
    const DURABLE = 'durable';

    /**
     * Restrict access to the queue to the connection used to declare it.
     */
    const EXCLUSIVE = 'exclusive';

    /**
     * Delete the queue once all consumers have been cancelled.
     */
    const AUTO_DELETE = 'auto_delete';

    public static function defaults()
    {
        return [self::EXCLUSIVE()->on()];
    }
}
