<?php
namespace Recoil\Amqp\Transport\Exchange;

final class UnbindMethod
{
    public $reserved; // short
    public $destination; // shortstr
    public $source; // shortstr
    public $routingKey; // shortstr
    public $nowait; // bit
    public $arguments; // table
}
