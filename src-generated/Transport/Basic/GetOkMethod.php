<?php
namespace Recoil\Amqp\Transport\Basic;

final class GetOkMethod
{
    public $deliveryTag; // longlong
    public $redelivered; // bit
    public $exchange; // shortstr
    public $routingKey; // shortstr
    public $messageCount; // long
}
