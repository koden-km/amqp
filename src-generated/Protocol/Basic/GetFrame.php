<?php
namespace Recoil\Amqp\Protocol\Basic;

use Recoil\Amqp\Protocol\Frame;

final class GetFrame extends Frame
{
    public $reserved; // short
    public $queue; // shortstr
    public $noAck; // bit
}
