<?php

namespace Recoil\Amqp;

use Icecave\Flip\OptionSetTrait;

/**
 * Options that affect the behavior of exchanges.
 */
final class ExchangeOptions
{
    use OptionSetTrait;

    /**
     * Persist the exchange across server restarts.
     */
    private $durable = false;

    /**
     * Delete the exchange once there are no remaining queues bound to it.
     *
     * This is a RabbitMQ specific extension to the AMQP protocol.
     */
    private $autoDelete = false;

    /**
     * Mark the exchange as internal. No messages can be published directly to
     * an internal exchange, rather it is the target for exchange-to-exchange
     * bindings.
     *
     * Exchange-to-exchange binding is a RabbitMQ specific extension to the AMQP
     * protocol.
     */
    private $internal = false;
}
