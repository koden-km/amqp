<?php
namespace Recoil\Amqp\Protocol\Channel;

use Recoil\Amqp\Protocol\Frame;

final class FlowOkFrame extends Frame
{
    public $active; // bit
}
