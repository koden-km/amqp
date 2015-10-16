<?php

namespace Recoil\Amqp;

use Recoil\Amqp\Exception\ConnectionException;

/**
 * A message consumer.
 */
interface Consumer
{
    /**
     * The channel that the consumer is using.
     *
     * @return Channel The channel.
     */
    public function channel();

    /**
     * Get the queue from which messages are consumed.
     *
     * @return Queue The source queue.
     */
    public function queue();

    /**
     * Get the consumer options.
     *
     * @return ConsumerOptions The consumer options.
     */
    public function options();

    /**
     * Get the consumer tag.
     *
     * @return string The consumer tag.
     */
    public function tag();

    /**
     * Stop consuming messages.
     *
     * @return null                [via promise] On success.
     * @throws ConnectionException [via promise] If not connected to the AMQP server.
     */
    public function cancel();
}
