<?php

namespace Recoil\Amqp;

use Icecave\Flip\OptionSetTrait;

/**
 * Represents the capabilities of the server.
 */
final class ServerCapabilities
{
    use OptionSetTrait;

    /**
     * @var boolean True if the server support per-consumer QoS limits.
     * @see https://www.rabbitmq.com/consumer-prefetch.html
     */
    private $perConsumerQos = false;

    /**
     * @var boolean True if the server supports exchane-to-exchange bindings.
     * @see https://www.rabbitmq.com/blog/2010/10/19/exchange-to-exchange-bindings/
     */
    private $exchangeToExchangeBindings = false;
}
