<?php

namespace Recoil\Amqp;

use Recoil\Amqp\Exception\ConnectionException;
use Recoil\Amqp\Exception\ResourceLockedException;
use Recoil\Amqp\Exception\ResourceNotFoundException;

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
     * Get the queue options.
     *
     * @return QueueOptions The queue options.
     */
    public function options();

    /**
     * Publish a message to this queue.
     *
     * This is a convenience method equivalent to publishing to the pre-declared,
     * nameless, direct exchange with a routing key equal to the queue name.
     *
     * @param Message             $message The message to publish.
     * @param PublishOptions|null $options Options that affect the publish operation, or null to use the defaults.
     * @param Channel|null        $channel The channel to use, or null to use an automatically managed channel.
     *
     * @throws ConnectionException      if not connected to the AMQP server.
     * @throws InvalidArgumentException If a routing key is required but not provided.
     */
    public function publish(
        Message $message,
        PublishOptions $options = null,
        Channel $channel = null
    );

    /**
     * Consume messages from this queue.
     *
     * Invokes a callback with a DeliveredMessage instance when a message
     * arrives.
     *
     * @see DeliveredMessage
     *
     * @param callable             $callback The callback to invoke when a message is received.
     * @param ConsumerOptions|null $options  Options that affect the behavior of the consumer, or null to use the defaults.
     * @param string               $tag      A unique identifier for the consumer, or an empty string use a random, unique tag.
     * @param Channel|null         $channel  The channel to use, or null to use an automatically managed channel.
     *
     * @return Consumer                  [via promise] The created consumer, can be used to cancel consumation.
     * @throws ResourceLockedException   [via promise] If another connection has an exclusive consumer.
     * @throws ResourceNotFoundException [via promise] If the queue does not exist on the server.
     * @throws ConnectionException       [via promise] If not connected to the AMQP server.
     */
    public function consume(
        callable $callback,
        ConsumerOptions $options = null,
        $tag = '',
        Channel $channel = null
    );

    /**
     * Delete this queue.
     *
     * @return null                [via promise] On success.
     * @throws ConnectionException [via promise] If not connected to the AMQP server.
     */
    public function delete();

    /**
     * Bind this queue to an exchange.
     *
     * @param Exchange $source     The exchange to bind to.
     * @param string   $routingKey The routing key for DIRECT and TOPIC exchanges, or empty string for FANOUT and HEADERS exchanges.
     *
     * @return null                     [via promise] On success.
     * @throws ConnectionException      [via promise] If not connected to the AMQP server.
     * @throws InvalidArgumentException [via promise] If a routing key is required but not provided.
     */
    public function bind(Exchange $source, $routingKey = '');

    /**
     * Unbind this queue from an exchange.
     *
     * @param Exchange $source     The exchange to unbind from.
     * @param string   $routingKey The routing key for DIRECT and TOPIC exchanges, or empty string for FANOUT and HEADERS exchanges.
     *
     * @return null                     [via promise] On success.
     * @throws ConnectionException      [via promise] If not connected to the AMQP server.
     * @throws InvalidArgumentException [via promise] If a routing key is required but not provided.
     */
    public function unbind(Exchange $source, $routingKey = '');
}
