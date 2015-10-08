<?php
namespace Recoil\Amqp\Protocol\Basic;

use Recoil\Amqp\Protocol\Frame;

final class NackFrame extends Frame
{
    public $deliveryTag; // longlong
    public $multiple; // bit
    public $requeue; // bit
}
