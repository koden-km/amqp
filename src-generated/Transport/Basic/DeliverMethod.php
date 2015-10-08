<?php
namespace Recoil\Amqp\Transport\Basic;

final class DeliverMethod
{
    public $consumerTag; // shortstr
    public $deliveryTag; // longlong
    public $redelivered; // bit
    public $exchange; // shortstr
    public $routingKey; // shortstr
}
