<?php
namespace Recoil\Amqp\Transport\Queue;

final class DeclareMethod
{
    public $reserved; // short
    public $queue; // shortstr
    public $passive; // bit
    public $durable; // bit
    public $exclusive; // bit
    public $autoDelete; // bit
    public $nowait; // bit
    public $arguments; // table
}
