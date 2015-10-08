<?php
namespace Recoil\Amqp\Protocol\Basic;

use Recoil\Amqp\Protocol\Frame;

final class ConsumeFrame extends Frame
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
