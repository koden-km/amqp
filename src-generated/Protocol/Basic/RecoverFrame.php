<?php
namespace Recoil\Amqp\Protocol\Basic;

use Recoil\Amqp\Protocol\Frame;

final class RecoverFrame extends Frame
{
    public $requeue; // bit
}
