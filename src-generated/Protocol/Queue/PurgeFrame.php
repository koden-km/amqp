<?php
namespace Recoil\Amqp\Protocol\Queue;

use Recoil\Amqp\Protocol\Frame;

final class PurgeFrame extends Frame
{
    public $reserved; // short
    public $queue; // shortstr
    public $nowait; // bit
}
