<?php
namespace Recoil\Amqp\Protocol\Exchange;

use Recoil\Amqp\Protocol\Frame;

final class DeleteFrame extends Frame
{
    public $reserved; // short
    public $exchange; // shortstr
    public $ifUnused; // bit
    public $nowait; // bit
}
