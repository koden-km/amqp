<?php
namespace Recoil\Amqp\Protocol\Confirm;

use Recoil\Amqp\Protocol\Frame;

final class SelectFrame extends Frame
{
    public $nowait; // bit
}
