<?php
namespace Recoil\Amqp\Protocol\Queue;

use Recoil\Amqp\Protocol\Frame;

final class DeclareFrame extends Frame
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
