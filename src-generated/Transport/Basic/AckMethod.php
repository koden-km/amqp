<?php
namespace Recoil\Amqp\Transport\Basic;

final class AckMethod
{
    public $deliveryTag; // longlong
    public $multiple; // bit
}
