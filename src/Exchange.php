<?php
namespace Recoil\Amqp;

use Icecave\Flip\OptionSet;
use InvalidArgumentException;

/**
 * An AMQP exchange.
 *
 * An exchange is the primary target for message publication.
 */
interface Exchange
{
    /**
     * Get the name of the exchange.
     *
     * @return string The exchange name.
     */
    public function name();

    /**
     * Get the exchange type.
     *
     * @return ExchangeType The exchange type.
     */
    public function type();

    /**
     * Get the options used when the exchange was declared.
     *
     * @return OptionSet The exchange options.
     */
    public function options();

    /**
     * Publish a message to this exchange.
     *
     * @param Message                   $message    The message to publish.
     * @param string                    $routingKey The routing key for DIRECT and TOPIC exchanges, or empty string for FANOUT and HEADERS exchanges.
     * @param array<PublishOption>|null $options    An array of options to set, or null to use the defaults.
     *
     * Via promise:
     * @throws ConnectionException      if not connected to the AMQP server.
     * @throws InvalidArgumentException if a routing key is required but not provided, and vice-versa.
     */
    public function publish(
        Message $message,
        $routingKey = '',
        array $options = null
    );

    /**
     * Delete this exchange.
     *
     * Via promise:
     * @throws ConnectionException if not connected to the AMQP server.
     * @throws LogicException      if the exchange is one of the pre-declared AMQP exchanges.
     */
    public function delete();
}
