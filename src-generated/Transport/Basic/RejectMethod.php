<?php
namespace Recoil\Amqp\Transport\Basic;

final class RejectMethod
{
    public $deliveryTag; // longlong
    public $requeue; // bit
}
