<?php
namespace Recoil\Amqp\Protocol\Channel;

use Recoil\Amqp\Protocol\Frame;

final class FlowFrame extends Frame
{
    public $active; // bit
}
