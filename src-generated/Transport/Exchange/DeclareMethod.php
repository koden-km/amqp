<?php
namespace Recoil\Amqp\Transport\Exchange;

final class DeclareMethod
{
    public $reserved; // short
    public $exchange; // shortstr
    public $type; // shortstr
    public $passive; // bit
    public $durable; // bit
    public $autoDelete; // bit
    public $internal; // bit
    public $nowait; // bit
    public $arguments; // table
}
