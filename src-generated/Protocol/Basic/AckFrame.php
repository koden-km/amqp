<?php
namespace Recoil\Amqp\Protocol\Basic;

use Recoil\Amqp\Protocol\Frame;

final class AckFrame extends Frame
{
    public $deliveryTag; // longlong
    public $multiple; // bit
}
