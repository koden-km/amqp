<?php
namespace Recoil\Amqp;

use Icecave\Flip\OptionSet;

/**
 * An AMQP queue.
 *
 * Messages are delivered to queues and are then read by consumers. A queue
 * receives messages by creating bindings to one or more exchanges.
 */
interface Queue
{
    /**
     * Get the name of the queue.
     *
     * @return string The queue name.
     */
    public function name();

    /**
     * Get the options used when the queue was declared.
     *
     * @return OptionSet The queue options.
     */
    public function options();

    /**
     * Bind this queue to an exchange.
     *
     * @param Exchange $exchange   The exchange.
     * @param string   $routingKey The routing key for DIRECT and TOPIC exchanges, or empty string for FANOUT and HEADERS exchanges.
     *
     * Via promise:
     * @return null
     * @throws ConnectionException      if not connected to the AMQP server.
     * @throws InvalidArgumentException if a routing key is required but not provided, and vice-versa.
     */
    public function bind(Exchange $exchange, $routingKey = '');

    /**
     * Unbind this queue from an exchange.
     *
     * @param Exchange $exchange   The exchange.
     * @param string   $routingKey The routing key for DIRECT and TOPIC exchanges, or empty string for FANOUT and HEADERS exchanges.
     *
     * Via promise:
     * @return null
     * @throws ConnectionException      if not connected to the AMQP server.
     * @throws InvalidArgumentException if a routing key is required but not provided, and vice-versa.
     */
    public function unbind(Exchange $exchange, $routingKey = '');

    /**
     * Publish a message to this queue.
     *
     * This is a convenience method equivalent to publishing to the pre-declared,
     * nameless, direct exchange with a routing key equal to the queue name.
     *
     * @param Message $message The message to publish.
     * @param mixed   $options Publish options.
     *
     * @see PublishOption
     *
     * Via promise:
     * @return null
     * @throws ConnectionException      if not connected to the AMQP server.
     * @throws InvalidArgumentException if a routing key is required but not provided, and vice-versa.
     */
    public function publish(Message $message, $options = []);

    /**
     * Consume messages from this queue.
     *
     * Invokes a callback when a message is received from this queue.
     *
     * The callback signature is $callback(ConsumerMessage $message).
     *
     * @param callable $callback The callback to invoke when a message is received.
     * @param mixed    $options  Consumer options.
     * @param string   $tag      A unique identifier for the consumer, or an empty string to have the server generate the consumer tag.
     *
     * @see ConsumerOption
     *
     * Via promise:
     * @return Consumer
     * @throws ResourceLockedException   if another connection has an exclusive consumer.
     * @throws ResourceNotFoundException if the queue does not exist on the server.
     * @throws ConnectionException       if not connected to the AMQP server.
     */
    public function consume(callable $callback, $options = [], $tag = '');

    /**
     * Delete this queue.
     *
     * Via promise:
     * @throws ConnectionException if not connected to the AMQP server.
     */
    public function delete();
}
