<?php
namespace Recoil\Amqp\Protocol\Basic;

use Recoil\Amqp\Protocol\Frame;

final class RejectFrame extends Frame
{
    public $deliveryTag; // longlong
    public $requeue; // bit
}
