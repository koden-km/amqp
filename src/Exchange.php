<?php
namespace Recoil\Amqp;

use InvalidArgumentException;

/**
 * An AMQP exchange.
 *
 * All messages are published to an exchange, and then routed to zero or more
 * queues based on the queue bindings.
 *
 * @see Queue::bind()
 *
 * RabbitMQ also supports exchange-to-exchange bindings.
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
     * Get the exchange options.
     *
     * @return ExchangeOptions The exchange options.
     */
    public function options();

    /**
     * Publish a message to this exchange.
     *
     * @param Message             $message    The message to publish.
     * @param string              $routingKey The routing key for DIRECT and TOPIC exchanges, or empty string for FANOUT and HEADERS exchanges.
     * @param PublishOptions|null $options    Options that affect the publish operation, or null to use the defaults.
     *
     * @throws ConnectionException      if not connected to the AMQP server.
     * @throws InvalidArgumentException if a routing key is required but not provided, and vice-versa.
     */
    public function publish(
        Message $message,
        $routingKey = '',
        PublishOptions $options = null
    );

    /**
     * Delete this exchange.
     *
     * Via promise:
     * @return null                on success.
     * @throws ConnectionException if not connected to the AMQP server.
     * @throws LogicException      if the exchange is one of the pre-declared AMQP exchanges.
     */
    public function delete();

    /**
     * Bind this exchange to another.
     *
     * @param Exchange $source     The exchange to bind to.
     * @param string   $routingKey The routing key for DIRECT and TOPIC exchanges, or empty string for FANOUT and HEADERS exchanges.
     *
     * Via promise:
     * @return null                     on success.
     * @throws ConnectionException      if not connected to the AMQP server.
     * @throws InvalidArgumentException if a routing key is required but not provided, and vice-versa.
     *
     * Exchange-to-exchange binding is a RabbitMQ specific extension to the AMQP
     * protocol.
     */
    public function bind(Exchange $source, $routingKey = '');

    /**
     * Unbind this exchange from another.
     *
     * @param Exchange $source     The exchange to unbind from.
     * @param string   $routingKey The routing key for DIRECT and TOPIC exchanges, or empty string for FANOUT and HEADERS exchanges.
     *
     * Via promise:
     * @return null                     on success.
     * @throws ConnectionException      if not connected to the AMQP server.
     * @throws InvalidArgumentException if a routing key is required but not provided, and vice-versa.
     *
     * Exchange-to-exchange binding is a RabbitMQ specific extension
     * to the AMQP protocol.
     */
    public function unbind(Exchange $exchange, $routingKey = '');
}
