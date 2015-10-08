<?php
namespace Recoil\Amqp\Protocol\Basic;

use Recoil\Amqp\Protocol\Frame;

final class CancelFrame extends Frame
{
    public $consumerTag; // shortstr
    public $nowait; // bit
}
