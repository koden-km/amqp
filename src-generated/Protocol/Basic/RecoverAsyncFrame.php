<?php
namespace Recoil\Amqp\Protocol\Basic;

use Recoil\Amqp\Protocol\Frame;

final class RecoverAsyncFrame extends Frame
{
    public $requeue; // bit
}
