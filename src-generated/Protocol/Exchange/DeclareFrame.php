<?php
namespace Recoil\Amqp\Protocol\Exchange;

use Recoil\Amqp\Protocol\Frame;

final class DeclareFrame extends Frame
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
