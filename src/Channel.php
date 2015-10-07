<?php
namespace Recoil\Amqp;

/**
 * An AMQP channel.
 */
interface Channel
{
    /**
     * Declare an exchange.
     *
     * To get one of the pre-declared exchanges, use the following methods:
     *
     * @see Channel::directExchange()
     * @see Channel::amqExchange()
     *
     * @param string       $name    The exchange name.
     * @param ExchangeType $type    The exchange type.
     * @param mixed        $options Declaration options.
     *
     * @see ExchangeOption
     *
     * Via promise:
     * @return Exchange            The exchange.
     * @throws DeclareException    if the exchange could not be declared because it already exists with different options.
     * @throws ConnectionException if not connected to the AMQP server.
     * @throws LogicException      if the channel has been closed.
     */
    public function exchange(
        $name,
        ExchangeType $type,
        $options = []
    );

    /**
     * Get the pre-declared, nameless, direct exchange.
     *
     * Every queue is automatically bound to the nameless exchange with a
     * routing key the same as the queue name.
     *
     * @link https://www.rabbitmq.com/tutorials/amqp-concepts.html#exchanges
     *
     * @see Channel::exchange()
     * @see Channel::amqExchange()
     *
     * Via promise:
     * @return Exchange            The pre-declared, nameless, direct exchange.
     * @throws ConnectionException if not connected to the AMQP server.
     * @throws LogicException      if the channel has been closed.
     */
    public function directExchange();

    /**
     * Get the pre-declared amq.* exchange of the given type.
     *
     * @link https://www.rabbitmq.com/tutorials/amqp-concepts.html#exchanges
     *
     * @see Channel::exchange()
     * @see Channel::directExchange()
     *
     * @param ExchangeType $type The exchange type.
     *
     * Via promise:
     * @return Exchange            The amq.* exchange.
     * @throws ConnectionException if not connected to the AMQP server.
     * @throws LogicException      if the channel has been closed.
     */
    public function amqExchange(ExchangeType $type);

    /**
     * Declare a queue.
     *
     * Called with no arguments, this method will return an exclusive,
     * auto-deleting queue with a server-generated name.
     *
     * @param string $name    The queue name, or an empty string to have the server generated a name.
     * @param mixed  $options Declaration options.
     *
     * @see QueueOption
     *
     * Via promise:
     * @return Queue                   The queue.
     * @throws DeclareException        if the queue could not be declared because it already exists with different options.
     * @throws ResourceLockedException if the queue already exists, but another connection has exclusive access.
     * @throws ConnectionException     if not connected to the AMQP server.
     * @throws LogicException          if the channel has been closed.
     */
    public function queue($name = '', array $options = []);

    /**
     * Set the channel's Quality-of-Service options.
     *
     * Please note that RabbitMQ's behaviour deviates from the AMQP
     * specification in its handling of the global flag:
     *
     * @link https://www.rabbitmq.com/consumer-prefetch.html
     *
     * @param integer|null $count The maximum number of un-acknowledged messages to accept, or null for unlimited.
     * @param integer|null $size  The maximum size of un-acknowledged messages to accept, in bytes, or null for unlimited.
     * @param QosScope     $scope The scope at which the change is applied.
     *
     * Via promise:
     * @return null
     * @throws ConnectionException if not connected to the AMQP server.
     * @throws LogicException      if the channel has been closed.
     */
    public function qos($count, $size = null, QosScope $scope = null);

    /**
     * Close the channel.
     *
     * Via promise:
     * @return null
     */
    public function close();
}
