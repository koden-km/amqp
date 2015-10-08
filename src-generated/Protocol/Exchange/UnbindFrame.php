<?php
namespace Recoil\Amqp\Protocol\Exchange;

use Recoil\Amqp\Protocol\Frame;

final class UnbindFrame extends Frame
{
    public $reserved; // short
    public $destination; // shortstr
    public $source; // shortstr
    public $routingKey; // shortstr
    public $nowait; // bit
    public $arguments; // table
}
