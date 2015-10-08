<?php
namespace Recoil\Amqp\Protocol\Connection;

use Recoil\Amqp\Protocol\Frame;

final class BlockedFrame extends Frame
{
    public $reason; // shortstr
}
