<?php
namespace Recoil\Amqp\Transport\Basic;

final class ConsumeMethod
{
    public $reserved; // short
    public $queue; // shortstr
    public $consumerTag; // shortstr
    public $noLocal; // bit
    public $noAck; // bit
    public $exclusive; // bit
    public $nowait; // bit
    public $arguments; // table
}
