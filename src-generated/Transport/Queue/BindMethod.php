<?php
namespace Recoil\Amqp\Transport\Queue;

final class BindMethod
{
    public $reserved; // short
    public $queue; // shortstr
    public $exchange; // shortstr
    public $routingKey; // shortstr
    public $nowait; // bit
    public $arguments; // table
}
