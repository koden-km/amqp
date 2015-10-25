<?php

namespace Recoil\Amqp;

use Icecave\Flip\OptionSetTrait;

/**
 * Options used to configure the behavior of message publishing.
 */
final class PublishOptions
{
    use OptionSetTrait;

    /**
     * Fail if the server is unable to route the message to any queues.
     *
     * If this option is OFF, messages that are not routed to any queues are
     * silently dropped.
     */
    private $mandatory = false;

    /**
     * Only place a message on a queue if there are currently ready consumers
     * on that queue.
     *
     * @deprecated This feature is no longer supported by RabbitMQ.
     * @see http://www.rabbitmq.com/blog/2012/11/19/breaking-things-with-rabbitmq-3-0/
     */
    private $immediate = false;
}
