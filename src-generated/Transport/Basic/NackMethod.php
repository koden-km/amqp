<?php
namespace Recoil\Amqp\Transport\Basic;

final class NackMethod
{
    public $deliveryTag; // longlong
    public $multiple; // bit
    public $requeue; // bit
}
