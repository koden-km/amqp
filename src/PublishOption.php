<?php
namespace Recoil\Amqp;

use Icecave\Flip\AbstractOption;

/**
 * Options used to configure the behaviour of message publishing.
 */
final class PublishOption extends AbstractOption
{
    /**
     * Fail if the server is unable to route the message to any queues.
     *
     * If this option is OFF, messages that are not routed to any queues are
     * silently dropped.
     */
    const MANDATORY = 'mandatory';

    /**
     * Only place a message on a queue if there are currently ready consumers
     * on that queue.
     *
     * @deprecated This feature is no longer supported by Rabbit MQ.
     * @link http://www.rabbitmq.com/blog/2012/11/19/breaking-things-with-rabbitmq-3-0/
     */
    const IMMEDIATE = 'immediate';
}
