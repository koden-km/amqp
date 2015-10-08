<?php
namespace Recoil\Amqp\Protocol\Queue;

use Recoil\Amqp\Protocol\Frame;

final class UnbindFrame extends Frame
{
    public $reserved; // short
    public $queue; // shortstr
    public $exchange; // shortstr
    public $routingKey; // shortstr
    public $arguments; // table
}
