<?php
namespace Recoil\Amqp;

use Icecave\Flip\AbstractOption;

/**
 * Options used when consuming from a queue.
 */
final class ConsumerOption extends AbstractOption
{
    /**
     * Do not consume messages published by the same connection as the consumer.
     */
    const NO_LOCAL = 'no_local';

    /**
     * Do not require the consumer to acknowledge messages.
     */
    const NO_ACK = 'no_ack';

    /**
     * Request exclusive access to the queue. This means that no other consumers
     * may exist on the queue.
     */
    const EXCLUSIVE = 'exclusive';
}
