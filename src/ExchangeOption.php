<?php
namespace Recoil\Amqp;

use Icecave\Flip\AbstractOption;

/**
 * Options used when declaring an exchange.
 */
final class ExchangeOption extends AbstractOption
{
    /**
     * Confirm that the exchange exists and matches the configured options, but
     * do not create it.
     */
    const PASSIVE = 'passive';

    /**
     * Persist the exchange across server restarts.
     */
    const DURABLE = 'durable';

    /**
     * Delete the exchange once there are no remaining queues bound to it.
     */
    const AUTO_DELETE = 'auto_delete';

    /**
     * Mark the exchange as internal. No messages can be published directly to
     * an internal exchange, rather it is the target for exchange-to-exchange
     * bindings.
     */
    const INTERNAL = 'internal';
}
