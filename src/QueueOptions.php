<?php
namespace Recoil\Amqp;

use Icecave\Flip\OptionSetTrait;

/**
 * Options that affect the behavior of queues.
 */
final class QueueOptions
{
    use OptionSetTrait;

    /**
     * Persist the queue (but not necessarily the messages on it) across server
     * restarts.
     */
    private $durable = false;

    /**
     * Restrict access to the queue to the connection used to declare it.
     */
    private $exclusive = true;

    /**
     * Delete the queue once all consumers have been cancelled.
     */
    private $autoDelete = false;
}
