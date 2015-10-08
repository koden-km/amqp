<?php
namespace Recoil\Amqp\Transport\Queue;

final class UnbindMethod
{
    public $reserved; // short
    public $queue; // shortstr
    public $exchange; // shortstr
    public $routingKey; // shortstr
    public $arguments; // table
}
