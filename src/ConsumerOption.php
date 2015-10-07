<?php
namespace Recoil\Amqp;

use Icecave\Flip\OptionSetTrait;

/**
 * Options used to control the behavior of consumers.
 */
final class ConsumerOption extends AbstractOption
{
    use OptionSetTrait;

    /**
     * Do not consume messages published by the same connection as the consumer.
     */
    private $noLocal = false;

    /**
     * Do not require the consumer to acknowledge messages.
     */
    private $noAck = false;

    /**
     * Request exclusive access to the queue. This means that no other consumers
     * may exist on the queue.
     */
    private $exclusive = false;
}
