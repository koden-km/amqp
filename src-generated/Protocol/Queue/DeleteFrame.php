<?php
namespace Recoil\Amqp\Protocol\Queue;

use Recoil\Amqp\Protocol\Frame;

final class DeleteFrame extends Frame
{
    public $reserved; // short
    public $queue; // shortstr
    public $ifUnused; // bit
    public $ifEmpty; // bit
    public $nowait; // bit
}
